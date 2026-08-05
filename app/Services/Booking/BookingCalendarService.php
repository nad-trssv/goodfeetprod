<?php

namespace App\Services\Booking;

use App\Models\Appointments;
use App\Models\RedDay;
use App\Models\Services;
use App\Models\SiteSettings;
use App\Models\UserSchedule;
use Carbon\Carbon;

class BookingCalendarService
{
    public function workHours(string $date, int|string|null $userId = null): ?array
    {
        $day = strtolower(Carbon::parse($date)->format('l'));
        if ($userId && $userId !== 'all') {
            $schedule = UserSchedule::where('user_id', $userId)->first();
            if ($schedule) {
                return ['start' => $schedule->{$day.'_start'}, 'end' => $schedule->{$day.'_end'}];
            }
        }

        $hours = $this->setting('work_hours');

        return $hours[$day] ?? null;
    }

    public function lunchHours(int|string|null $userId = null): array
    {
        if ($userId && $userId !== 'all') {
            $schedule = UserSchedule::where('user_id', $userId)->first();
            if ($schedule?->lunch_start && $schedule?->lunch_end) {
                return ['start' => $schedule->lunch_start, 'end' => $schedule->lunch_end];
            }
        }

        return $this->setting('lunch_hours') ?? ['start' => null, 'end' => null];
    }

    public function bookingLimit(): array
    {
        return $this->setting('booking_date_limit') ?? ['days' => 30, 'active' => false];
    }

    public function partialClosures(string $date, int|string|null $userId = null): array
    {
        $parsed = Carbon::parse($date);
        $query = RedDay::where('full_day', false)->where(fn($query)=>$query->where(function($range)use($date){$range->where('repeat',false)->whereDate('date','<=',$date)->where(fn($until)=>$until->whereNull('date_to')->orWhereDate('date_to','>=',$date));})->orWhere('repeat',true));
        $userId && $userId !== 'all' ? $query->visibleFor($userId) : $query->whereNull('user_id');
        return $query->get()->filter(fn(RedDay $closure)=>$this->coversDate($closure,$parsed))->values()->toArray();
    }

    public function isClosed(string $date, int|string|null $userId = null): bool
    {
        $parsed=Carbon::parse($date);
        $query = RedDay::where('full_day', true)->where(fn($query)=>$query->where(function($range)use($date){$range->where('repeat',false)->whereDate('date','<=',$date)->where(fn($until)=>$until->whereNull('date_to')->orWhereDate('date_to','>=',$date));})->orWhere('repeat',true));
        $userId && $userId !== 'all' ? $query->visibleFor($userId) : $query->whereNull('user_id');
        return $query->get()->contains(fn(RedDay $closure)=>$this->coversDate($closure,$parsed));
    }

    public function coversDate(RedDay $closure, Carbon $target): bool
    {
        $from=Carbon::parse($closure->date);$to=Carbon::parse($closure->date_to?:$closure->date);
        if(!$closure->repeat)return $target->betweenIncluded($from,$to);
        $length=$from->diffInDays($to);
        foreach([$target->year-1,$target->year] as $year){try{$occurrence=$from->copy()->year($year);if($target->betweenIncluded($occurrence,$occurrence->copy()->addDays($length)))return true;}catch(\Throwable){}}
        return false;
    }

    public function serviceDuration(int $serviceId, string $date): ?int
    {
        $service = Services::with('rules')->find($serviceId);
        if (! $service) {
            return null;
        }

        return (int) ($service->ruleForDate($date)?->duration_minutes ?? $service->duration_minutes);
    }

    public function bookedEvents(int|string $userId, string $date): array
    {
        $query = Appointments::with('service')->whereDate('appointment_start', $date);
        if ($userId !== 'all') {
            $query->where('user_id', $userId);
        }

        return $query->get()->map(fn (Appointments $appointment) => [
            'id' => $appointment->id,
            'title' => $appointment->service->name,
            'user_id' => $appointment->user_id,
            'client_phone' => $appointment->client_phone,
            'client_name' => $appointment->client_name,
            'client_lastname' => $appointment->client_lastname,
            'service_id' => $appointment->service_id,
            'textColor' => $appointment->service->eventColor,
            'description' => $appointment->description,
            'price' => $appointment->price,
            'start' => $appointment->appointment_start,
            'end' => $appointment->appointment_end,
        ])->all();
    }

    private function setting(string $key): ?array
    {
        $payload = SiteSettings::where('group', 'hours')->where('key', $key)->value('payload');

        return $payload ? json_decode($payload, true) : null;
    }
}
