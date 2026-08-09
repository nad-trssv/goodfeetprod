<?php

namespace App\Services;

use App\Http\Requests\Member\StoreRequest;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use App\Models\UserSchedule;
use App\Models\Appointments;
use App\Models\RedDay;
use App\Services\Booking\BookingCalendarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MemberService
{
    public $member;
    public $token;

    public function __construct(private readonly BookingCalendarService $calendar) {}

    public function list()
    {
        try {
            $now = now();
            $members = User::query()
                ->with([
                    'schedule',
                    'role',
                    'vacationClosures',
                    'currentAppointment.service:id,name',
                    'nextAppointment.service:id,name',
                    'actionRequiredAppointment.service:id,name',
                ])
                ->withCount('services')
                ->whereHas('role', fn ($query) => $query->where(function ($roles) {
                    $roles->whereNull('slug')->whereIn('id', [1, 2])
                        ->orWhere('slug', '!=', 'customer');
                }))
                ->orderBy('name')
                ->get();

            $closures = RedDay::query()
                ->where(fn ($query) => $query
                    ->where(fn ($range) => $range->where('repeat', false)->whereDate('date', '<=', $now->toDateString())->where(fn ($until) => $until->whereNull('date_to')->orWhereDate('date_to', '>=', $now->toDateString())))
                    ->orWhere('repeat', true))
                ->get()
                ->filter(fn (RedDay $closure) => $this->calendar->coversDate($closure, $now));

            $todayStats = Appointments::query()
                ->whereDate('appointment_start', $now->toDateString())
                ->whereIn('user_id', $members->pluck('id'))
                ->groupBy('user_id')
                ->selectRaw("user_id,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) completed_count,
                    SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) no_show_count,
                    SUM(CASE WHEN status IN ('cancelled_by_client','cancelled_by_business') THEN 1 ELSE 0 END) cancelled_count,
                    COALESCE(SUM(CASE WHEN status = 'completed' THEN price ELSE 0 END),0) earned_amount,
                    COALESCE(SUM(CASE WHEN status = 'no_show' THEN price ELSE 0 END),0) lost_amount")
                ->get()->keyBy('user_id');

            $members->each(function (User $member) use ($closures, $now, $todayStats) {
                $visibleClosures = $closures->filter(fn (RedDay $closure) => $closure->user_id === null || (int) $closure->user_id === (int) $member->id);
                $member->setAttribute('work_state', $this->workState($member, $visibleClosures, $now));
                $stats = $todayStats->get($member->id);
                $member->setAttribute('today_stats', [
                    'completed' => (int) ($stats?->completed_count ?? 0),
                    'no_show' => (int) ($stats?->no_show_count ?? 0),
                    'cancelled' => (int) ($stats?->cancelled_count ?? 0),
                    'earned' => (float) ($stats?->earned_amount ?? 0),
                    'lost' => (float) ($stats?->lost_amount ?? 0),
                ]);
            });
            return [
                'members' => $members,
                'summary' => [
                    'total' => $members->count(),
                    'online' => $members->filter->isOnline()->count(),
                    'available' => $members->where('work_state.code', 'available')->count(),
                    'busy' => $members->where('work_state.code', 'busy')->count(),
                    'not_working' => $members->whereIn('work_state.code', ['not_working', 'vacation'])->count(),
                ],
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    private function workState(User $member, $closures, Carbon $now): array
    {
        $vacation = $member->currentVacation($now);
        if ($vacation) {
            return ['code' => 'vacation', 'label' => 'В отпуске', 'detail' => $vacation->endDate()->format('d.m.Y')];
        }

        if ($closures->contains(fn (RedDay $closure) => $closure->full_day)) {
            return ['code' => 'not_working', 'label' => 'Сегодня не работает', 'detail' => 'Закрытый день'];
        }

        $day = strtolower($now->format('l'));
        $schedule = $member->schedule;
        $start = $schedule?->{$day.'_start'};
        $end = $schedule?->{$day.'_end'};
        if (! $start || ! $end) {
            return ['code' => 'not_working', 'label' => 'Сегодня выходной', 'detail' => null];
        }

        $workStart = Carbon::parse($now->toDateString().' '.$start);
        $workEnd = Carbon::parse($now->toDateString().' '.$end);
        $hours = substr($start, 0, 5).'–'.substr($end, 0, 5);
        if (! $now->betweenIncluded($workStart, $workEnd)) {
            return ['code' => 'not_working', 'label' => $now->lt($workStart) ? 'Смена ещё не началась' : 'Смена завершена', 'detail' => $hours];
        }

        if ($schedule?->lunch_start && $schedule?->lunch_end && $now->between(
            Carbon::parse($now->toDateString().' '.$schedule->lunch_start),
            Carbon::parse($now->toDateString().' '.$schedule->lunch_end)
        )) {
            return ['code' => 'break', 'label' => 'Обеденный перерыв', 'detail' => $hours];
        }

        if ($closures->contains(function (RedDay $closure) use ($now) {
            if ($closure->full_day || ! $closure->start_time || ! $closure->end_time) return false;
            return $now->between(
                Carbon::parse($now->toDateString().' '.$closure->start_time),
                Carbon::parse($now->toDateString().' '.$closure->end_time)
            );
        })) {
            return ['code' => 'break', 'label' => 'Личное закрытое время', 'detail' => $hours];
        }

        if ($member->currentAppointment) {
            return ['code' => 'busy', 'label' => 'Сейчас с клиентом', 'detail' => $member->currentAppointment->appointment_end->format('H:i')];
        }

        return ['code' => 'available', 'label' => 'Работает, сейчас свободен', 'detail' => $hours];
    }

    public function create()
    {
        try {
            return [
                'roles' => Roles::query()->where(fn ($query) => $query->whereNull('slug')->orWhere('slug', '!=', 'customer'))->orderBy('name')->get(),
                'services' => Services::where('status', 1)->orderBy('name')->get(),
            ];
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $this->member = User::create([
                    'username' => $request['username'],
                    'name' => $request['name'],
                    'phone' => $request['phone'],
                    'date_birthday' => $request['date_birthday'],
                    'role_id' => $request['role_id'],
                    'email' => $request['email'],
                    'password' => Hash::make($request['password']),
                    'profile_photo_path' => $request['profile_photo_path'],
                ]);

                // Привязка услуг
                if ($request->has('services') && is_array($request['services'])) {
                    $this->member->services()->sync($request['services']);
                }

                // Сохранение расписания
                UserSchedule::create([
                    'user_id' => $this->member->id,
                    'monday_start' => $request['monday_start'] ?? null,
                    'monday_end' => $request['monday_end'] ?? null,
                    'tuesday_start' => $request['tuesday_start'] ?? null,
                    'tuesday_end' => $request['tuesday_end'] ?? null,
                    'wednesday_start' => $request['wednesday_start'] ?? null,
                    'wednesday_end' => $request['wednesday_end'] ?? null,
                    'thursday_start' => $request['thursday_start'] ?? null,
                    'thursday_end' => $request['thursday_end'] ?? null,
                    'friday_start' => $request['friday_start'] ?? null,
                    'friday_end' => $request['friday_end'] ?? null,
                    'saturday_start' => $request['saturday_start'] ?? null,
                    'saturday_end' => $request['saturday_end'] ?? null,
                    'sunday_start' => $request['sunday_start'] ?? null,
                    'sunday_end' => $request['sunday_end'] ?? null,
                    'lunch_start' => $request['lunch_start'] ?? null,
                    'lunch_end' => $request['lunch_end'] ?? null,
                ]);

                $this->token = $this->member->createToken('auth_token')->plainTextToken;
            });

            return [
                'member' => $this->member,
                'token' => $this->token,
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
