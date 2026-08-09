<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointments;
use App\Models\RedDay;
use App\Models\SiteSettings;
use App\Models\User;
use App\Models\UserSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\EmployeeScheduleManager;

class MasterScheduleController extends Controller
{
    public function __construct(private readonly EmployeeScheduleManager $scheduleManager) {}
    public function index()
    {
        $user = Auth::user();
        $schedule = UserSchedule::where('user_id', $user->id)->first();

        $bookingLimit = SiteSettings::where('group', 'hours')
            ->where('key', 'booking_date_limit')
            ->first();
        $bookingLimit = $bookingLimit ? json_decode($bookingLimit->payload, true) : ['days' => 30, 'active' => false];

        $fixedBooking = SiteSettings::where('group', 'hours')
            ->where('key', 'fixed_booking_hours')
            ->first();
        $fixedBooking = $fixedBooking ? json_decode($fixedBooking->payload, true) : ['value' => false, 'payload' => []];

        $redDays = RedDay::where('user_id', $user->id)
            ->orderByDesc('date')
            ->get();

        return view('admin.master.schedule', [
            'schedule' => $schedule,
            'bookingLimit' => $bookingLimit,
            'fixedBooking' => $fixedBooking,
            'redDays' => $redDays,
            'user' => $user,
        ]);
    }

    public function updateWorkHours(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();
        $existing = $user->schedule;
        $data['lunch_start'] = $existing?->lunch_start;
        $data['lunch_end'] = $existing?->lunch_end;
        $this->scheduleManager->updateSchedule($user, $data);

        return response()->json(['status' => 'success']);
    }

    public function updateLunchHours(Request $request)
    {
        $user = Auth::user();
        $schedule = $user->schedule;
        $data = ['lunch_start' => $request->lunch_start ?: null, 'lunch_end' => $request->lunch_end ?: null];
        foreach (EmployeeScheduleManager::DAYS as $day) {
            $data[$day.'_off'] = !($schedule?->{$day.'_start'} && $schedule?->{$day.'_end'});
            $data[$day.'_start'] = $schedule?->{$day.'_start'};
            $data[$day.'_end'] = $schedule?->{$day.'_end'};
        }
        $this->scheduleManager->updateSchedule($user, $data);

        return response()->json(['status' => 'success']);
    }

    public function storeRedDay(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date',
            'type' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(RedDay::TYPES))],
            'start_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'repeat' => 'required|boolean',
        ]);

        $startTime = $request->start_time ? substr($request->start_time, 0, 5) : null;
        $endTime = $request->end_time ? substr($request->end_time, 0, 5) : null;

        if ($startTime && $endTime) {
            if (strtotime($endTime) <= strtotime($startTime)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Время окончания должно быть позже времени начала'
                ], 422);
            }
        }

        $fullDay = !$startTime || !$endTime;

        $redDay = RedDay::create([
            'name' => $request->name,
            'date' => $request->date,
            'date_to' => $request->date_to ?: $request->date,
            'type' => array_key_exists((string) $request->type, RedDay::TYPES) ? $request->type : 'other',
            'description' => $request->description,
            'start_time' => $fullDay ? null : $startTime,
            'end_time' => $fullDay ? null : $endTime,
            'full_day' => $fullDay,
            'repeat' => $request->repeat,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'status' => 'success',
            'redDay' => $redDay,
        ]);
    }

    public function updateFixedBooking(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'fixed_booking_enabled' => 'required|boolean',
            'fixed_booking_slots' => 'nullable|array',
            'fixed_booking_slots.*' => 'date_format:H:i',
        ]);

        UserSchedule::updateOrCreate(
            ['user_id' => $user->id],
            [
                'fixed_booking_enabled' => $request->fixed_booking_enabled,
                'fixed_booking_slots' => $request->fixed_booking_slots ?? [],
            ]
        );

        return response()->json(['status' => 'success']);
    }

    public function destroyRedDay(string $id)
    {
        $user = Auth::user();
        $redDay = RedDay::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $redDay->delete();

        return response()->json(['status' => 'success']);
    }

    public function updateRedDay(Request $request, string $id)
    {
        $user = Auth::user();
        $redDay = RedDay::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date',
            'type' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(RedDay::TYPES))],
            'start_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'repeat' => 'required|boolean',
        ]);

        $startTime = $request->start_time ? substr($request->start_time, 0, 5) : null;
        $endTime = $request->end_time ? substr($request->end_time, 0, 5) : null;

        if ($startTime && $endTime) {
            if (strtotime($endTime) <= strtotime($startTime)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Время окончания должно быть позже времени начала'
                ], 422);
            }
        }

        $fullDay = !$startTime || !$endTime;

        $redDay->update([
            'name' => $request->name,
            'date' => $request->date,
            'date_to' => $request->date_to ?: $request->date,
            'type' => array_key_exists((string) $request->type, RedDay::TYPES) ? $request->type : 'other',
            'start_time' => $fullDay ? null : $startTime,
            'end_time' => $fullDay ? null : $endTime,
            'full_day' => $fullDay,
            'repeat' => $request->repeat,
        ]);

        return response()->json(['status' => 'success', 'redDay' => $redDay->fresh()]);
    }

    public function allRedDays(Request $request)
    {
        $redDays = RedDay::with('user')
            ->orderByDesc('date')
            ->get()
            ->map(function ($day) {
                return [
                    'id' => $day->id,
                    'name' => $day->name,
                    'date' => \Carbon\Carbon::parse($day->date)->format('Y-m-d'),
                    'date_to' => $day->date_to ? \Carbon\Carbon::parse($day->date_to)->format('Y-m-d') : \Carbon\Carbon::parse($day->date)->format('Y-m-d'),
                    'type' => $day->type ?: 'other',
                    'type_label' => $day->typeLabel(),
                    'start_time' => $day->start_time ? substr($day->start_time, 0, 5) : null,
                    'end_time' => $day->end_time ? substr($day->end_time, 0, 5) : null,
                    'full_day' => $day->full_day,
                    'repeat' => $day->repeat,
                    'master_name' => $day->user ? $day->user->name : 'Общий',
                    'user_id' => $day->user_id,
                ];
            });

        return view('admin.red-days.index', [
            'redDays' => $redDays,
        ]);
    }
    public function storeRedDayForMaster(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date',
            'date_to' => 'nullable|date|after_or_equal:date',
            'type' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(RedDay::TYPES))],
            'user_id' => 'nullable|exists:users,id',
            'full_day' => 'nullable|boolean',
            'start_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'repeat' => 'nullable|boolean',
        ], [
            'name.required' => 'Заполните название',
            'date.required' => 'Заполните дату',
        ]);

        $fullDay = $request->has('full_day') && $request->full_day == '1';
        $startTime = $request->start_time ? substr($request->start_time, 0, 5) : null;
        $endTime = $request->end_time ? substr($request->end_time, 0, 5) : null;

        if (!$fullDay) {
            if (!$startTime || !$endTime) {
                return redirect()->route('admin.red-days.index')
                    ->with('error', 'Укажите время начала и конца');
            }
            if (strtotime($endTime) <= strtotime($startTime)) {
                return redirect()->route('admin.red-days.index')
                    ->with('error', 'Время окончания должно быть позже времени начала');
            }
        }

        RedDay::create([
            'name' => $request->name,
            'date' => $request->date,
            'user_id' => $request->user_id ?: null,
            'date_to' => $request->date_to ?: $request->date,
            'type' => array_key_exists((string) $request->type, RedDay::TYPES) ? $request->type : ($request->user_id ? 'other' : 'company_closure'),
            'full_day' => $fullDay,
            'start_time' => $fullDay ? null : $startTime,
            'end_time' => $fullDay ? null : $endTime,
            'repeat' => $request->has('repeat') ? 1 : 0,
        ]);

        return redirect()->route('admin.red-days.index')
            ->with('success', 'Запись добавлена!');
    }
    public function timeOff(Request $request)
    {
        $user = Auth::user();
        $redDays = RedDay::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereNull('user_id');
        })->orderByDesc('date')->get();

        $success = session('success');
        $error = session('error');
        $editId = $request->query('edit');
        $editDay = $editId ? RedDay::find($editId) : null;

        return view('admin.master.time-off', [
            'redDays' => $redDays,
            'user' => $user,
            'success' => $success,
            'error' => $error,
            'editDay' => $editDay,
        ]);
    }

    public function storeTimeOff(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date',
            'date_to' => 'nullable|date|after_or_equal:date',
            'type' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(RedDay::TYPES))],
            'full_day' => 'nullable|boolean',
            'start_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'repeat' => 'nullable|boolean',
        ], [
            'name.required' => 'Заполните название',
            'date.required' => 'Заполните дату',
        ]);

        $fullDay = $request->has('full_day') && $request->full_day == '1';
        $startTime = $request->start_time ? substr($request->start_time, 0, 5) : null;
        $endTime = $request->end_time ? substr($request->end_time, 0, 5) : null;

        if (!$fullDay) {
            if (!$startTime || !$endTime) {
                return redirect()->route('master.time-off.index')
                    ->with('error', 'Укажите время начала и конца');
            }
            if (strtotime($endTime) <= strtotime($startTime)) {
                return redirect()->route('master.time-off.index')
                    ->with('error', 'Время окончания должно быть позже времени начала');
            }
        }

        RedDay::create([
            'name' => $request->name,
            'date' => $request->date,
            'user_id' => $user->id,
            'date_to' => $request->date_to ?: $request->date,
            'type' => array_key_exists((string) $request->type, RedDay::TYPES) ? $request->type : 'other',
            'full_day' => $fullDay,
            'start_time' => $fullDay ? null : $startTime,
            'end_time' => $fullDay ? null : $endTime,
            'repeat' => $request->has('repeat') ? 1 : 0,
        ]);

        return redirect()->route('master.time-off.index')
            ->with('success', 'Запись добавлена!');
    }

    public function updateTimeOff(Request $request, string $id)
    {
        $user = Auth::user();
        if ($user->hasAllAppointmentsScope()) {
            $redDay = RedDay::where('id', $id)->firstOrFail();
        } else {
            $redDay = RedDay::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        }

        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date',
            'date_to' => 'nullable|date|after_or_equal:date',
            'type' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(RedDay::TYPES))],
            'full_day' => 'nullable|boolean',
            'start_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'repeat' => 'nullable|boolean',
        ], [
            'name.required' => 'Заполните название',
            'date.required' => 'Заполните дату',
        ]);

        $fullDay = $request->has('full_day') && $request->full_day == '1';
        $startTime = $request->start_time ? substr($request->start_time, 0, 5) : null;
        $endTime = $request->end_time ? substr($request->end_time, 0, 5) : null;

        if (!$fullDay) {
            if (!$startTime || !$endTime) {
                return redirect()->route('master.time-off.index')
                    ->with('error', 'Укажите время начала и конца');
            }
            if (strtotime($endTime) <= strtotime($startTime)) {
                return redirect()->route('master.time-off.index')
                    ->with('error', 'Время окончания должно быть позже времени начала');
            }
        }

        $redDay->update([
            'name' => $request->name,
            'date' => $request->date,
            'date_to' => $request->date_to ?: $request->date,
            'type' => array_key_exists((string) $request->type, RedDay::TYPES) ? $request->type : 'other',
            'full_day' => $fullDay,
            'start_time' => $fullDay ? null : $startTime,
            'end_time' => $fullDay ? null : $endTime,
            'repeat' => $request->has('repeat') ? 1 : 0,
        ]);

        return redirect()->route('master.time-off.index')
            ->with('success', 'Запись обновлена!');
    }

    public function destroyTimeOff(string $id)
    {
        $user = Auth::user();
        if ($user->hasAllAppointmentsScope()) {
            $redDay = RedDay::where('id', $id)->firstOrFail();
        } else {
            $redDay = RedDay::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        }
        $redDay->delete();

        return redirect()->route('master.time-off.index')
            ->with('success', 'Запись удалена!');
    }
    public function updateRedDayForMaster(Request $request, string $id)
    {
        $redDay = RedDay::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date',
            'date_to' => 'nullable|date|after_or_equal:date',
            'type' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(RedDay::TYPES))],
            'full_day' => 'nullable|boolean',
            'start_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'repeat' => 'nullable|boolean',
        ], [
            'name.required' => 'Заполните название',
            'date.required' => 'Заполните дату',
        ]);

        $fullDay = $request->has('full_day') && $request->full_day == '1';
        $startTime = $request->start_time ? substr($request->start_time, 0, 5) : null;
        $endTime = $request->end_time ? substr($request->end_time, 0, 5) : null;

        if (!$fullDay) {
            if (!$startTime || !$endTime) {
                return redirect()->route('admin.red-days.index')
                    ->with('error', 'Укажите время начала и конца');
            }
            if (strtotime($endTime) <= strtotime($startTime)) {
                return redirect()->route('admin.red-days.index')
                    ->with('error', 'Время окончания должно быть позже времени начала');
            }
        }

        $redDay->update([
            'name' => $request->name,
            'date' => $request->date,
            'date_to' => $request->date_to ?: $request->date,
            'type' => array_key_exists((string) $request->type, RedDay::TYPES) ? $request->type : 'other',
            'full_day' => $fullDay,
            'start_time' => $fullDay ? null : $startTime,
            'end_time' => $fullDay ? null : $endTime,
            'repeat' => $request->has('repeat') ? 1 : 0,
        ]);

        return redirect()->route('admin.red-days.index')
            ->with('success', 'Запись обновлена!');
    }

    public function destroyRedDayForMaster(string $id)
    {
        $redDay = RedDay::findOrFail($id);
        $redDay->delete();

        return redirect()->route('admin.red-days.index')
            ->with('success', 'Запись удалена!');
    }
    public function mastersToday(Request $request, $date = null)
    {
        $today = $date ? \Carbon\Carbon::parse($date) : \Carbon\Carbon::now();
        $todayStr = $today->format('Y-m-d');
        $dayOfWeek = strtolower($today->format('l'));

        $masters = User::with(['schedule', 'services'])
            ->whereHas('role', fn ($query) => $query->where('is_service_provider', true)->orWhereIn('id', [1, 2]))
            ->orderBy('name')
            ->get()
            ->map(function ($master) use ($today, $todayStr, $dayOfWeek) {

                $schedule = $master->schedule;

                // Рабочие часы сегодня
                $workStart = $schedule ? $schedule->{$dayOfWeek . '_start'} : null;
                $workEnd = $schedule ? $schedule->{$dayOfWeek . '_end'} : null;

                // Обед
                $lunchStart = $schedule ? $schedule->lunch_start : null;
                $lunchEnd = $schedule ? $schedule->lunch_end : null;

                // Статус дня
                $isFullDayClosed = RedDay::where('full_day', 1)
                    ->visibleFor($master->id)
                    ->where(function ($q) use ($today, $todayStr) {
                        $q->whereDate('date', $todayStr)
                            ->orWhere(function ($q2) use ($today) {
                                $q2->where('repeat', 1)
                                    ->whereMonth('date', $today->month)
                                    ->whereDay('date', $today->day);
                            });
                    })->exists();

                // Закрытые окна сегодня
                $closedWindows = RedDay::where('full_day', 0)
                    ->visibleFor($master->id)
                    ->where(function ($q) use ($today, $todayStr) {
                        $q->whereDate('date', $todayStr)
                            ->orWhere(function ($q2) use ($today) {
                                $q2->where('repeat', 1)
                                    ->whereMonth('date', $today->month)
                                    ->whereDay('date', $today->day);
                            });
                    })->get();

                // Записи сегодня
                $appointments = Appointments::with('service')
                    ->where('user_id', $master->id)
                    ->whereDate('appointment_start', $todayStr)
                    ->orderBy('appointment_start')
                    ->get();

                // Определяем статус
                if ($isFullDayClosed) {
                    $status = 'closed';
                } elseif (!$workStart || !$workEnd) {
                    $status = 'dayoff';
                } else {
                    $status = 'working';
                }

                return [
                    'id' => $master->id,
                    'name' => $master->name,
                    'photo' => $master->profile_photo_url,
                    'last_active' => $master->last_active,
                    'status' => $status,
                    'work_start' => $workStart ? substr($workStart, 0, 5) : null,
                    'work_end' => $workEnd ? substr($workEnd, 0, 5) : null,
                    'lunch_start' => $lunchStart ? substr($lunchStart, 0, 5) : null,
                    'lunch_end' => $lunchEnd ? substr($lunchEnd, 0, 5) : null,
                    'appointments' => $appointments,
                    'closed_windows' => $closedWindows,
                ];
            });

        return view('admin.master.today', [
            'masters' => $masters,
            'today' => $today,
        ]);
    }
}
