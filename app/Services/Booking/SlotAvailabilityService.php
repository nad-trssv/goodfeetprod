<?php

namespace App\Services\Booking;

use App\Models\Appointments;
use App\Models\Services;
use App\Models\SiteSettings;
use App\Models\UserSchedule;
use Carbon\Carbon;

class SlotAvailabilityService
{
    public function __construct(
        private readonly BookingCalendarService $calendar,
        private readonly RoomAllocationService $rooms,
    ) {
    }

    public function slots(string $date, int $serviceId, int $userId): array
    {
        $service = Services::query()
            ->where('status', true)
            ->where('is_deleted', false)
            ->whereHas('users', fn ($query) => $query->whereKey($userId))
            ->find($serviceId);

        if (! $service || $this->calendar->isClosed($date, $userId)) {
            return [];
        }

        $workHours = $this->calendar->workHours($date, $userId);
        $duration = $this->calendar->serviceDuration($serviceId, $date);
        if (! $duration || empty($workHours['start']) || empty($workHours['end'])) {
            return [];
        }

        $day = Carbon::parse($date)->startOfDay();
        $workStart = $this->at($day, $workHours['start']);
        $workEnd = $this->at($day, $workHours['end']);
        $lunch = $this->calendar->lunchHours($userId);
        $lunchStart = empty($lunch['start']) ? null : $this->at($day, $lunch['start']);
        $lunchEnd = empty($lunch['end']) ? null : $this->at($day, $lunch['end']);
        $closures = collect($this->calendar->partialClosures($date, $userId))->map(fn (array $closure) => [
            $this->at($day, $closure['start_time']),
            $this->at($day, $closure['end_time']),
        ]);
        $appointments = Appointments::where('user_id', $userId)
            ->whereDate('appointment_start', $date)
            ->get(['appointment_start', 'appointment_end']);

        $starts = $this->candidateStarts($service, $userId, $day, $workStart, $workEnd);
        $slots = [];

        foreach ($starts as $start) {
            $end = $start->copy()->addMinutes($duration);
            if ($start->lt($workStart) || $end->gt($workEnd) || ($day->isToday() && $start->lte(now()))) {
                continue;
            }
            if ($lunchStart && $lunchEnd && $this->overlaps($start, $end, $lunchStart, $lunchEnd)) {
                continue;
            }
            if ($closures->contains(fn (array $closure) => $this->overlaps($start, $end, $closure[0], $closure[1]))) {
                continue;
            }
            if ($appointments->contains(fn (Appointments $appointment) => $this->overlaps(
                $start,
                $end,
                Carbon::parse($appointment->appointment_start),
                Carbon::parse($appointment->appointment_end),
            ))) {
                continue;
            }
            $slots[] = ['start' => $start->format('H:i'), 'end' => $end->format('H:i')];
        }

        return $this->rooms->filterAvailableSlots($userId, $date, $slots);
    }

    private function candidateStarts(Services $service, int $userId, Carbon $day, Carbon $workStart, Carbon $workEnd): array
    {
        $schedule = UserSchedule::where('user_id', $userId)->first();
        if ($schedule?->fixed_booking_enabled && ! empty($schedule->fixed_booking_slots)) {
            return collect($schedule->fixed_booking_slots)->map(fn ($time) => $this->at($day, $time))->all();
        }

        $fixedSetting = SiteSettings::where('group', 'hours')->where('key', 'fixed_booking_hours')->value('payload');
        $fixed = $fixedSetting ? json_decode($fixedSetting, true) : null;
        if ($this->enabled($fixed['value'] ?? false) && ! empty($fixed['payload'])) {
            return collect($fixed['payload'])->map(fn ($time) => $this->at($day, $time))->all();
        }

        $start = $service->has_fixed_time && $service->time_from
            ? $this->at($day, $service->time_from)
            : $workStart->copy();
        $limit = $service->has_fixed_time && $service->time_to
            ? $this->at($day, $service->time_to)
            : $workEnd->copy();
        $starts = [];
        for ($time = $start; $time->lt($limit); $time->addMinutes(30)) {
            $starts[] = $time->copy();
        }

        return $starts;
    }

    private function at(Carbon $day, string $time): Carbon
    {
        return $day->copy()->setTimeFromTimeString($time);
    }

    private function overlaps(Carbon $start, Carbon $end, Carbon $blockedStart, Carbon $blockedEnd): bool
    {
        return $start->lt($blockedEnd) && $end->gt($blockedStart);
    }

    private function enabled(mixed $value): bool
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
