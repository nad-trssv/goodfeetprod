<?php

namespace App\Services\Booking;

use App\Models\Appointments;
use App\Models\AppointmentRescheduleRequest;
use App\Models\AppointmentSlotHold;
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

    public function slots(string $date, int $serviceId, int $userId, ?int $excludedAppointmentId = null, ?int $excludedRescheduleRequestId = null, ?string $excludedHoldToken = null): array
    {
        $day = Carbon::parse($date)->startOfDay();
        $limit = max(0, (int) ($this->calendar->bookingLimit()['days'] ?? 30));
        if ($day->lt(today()) || $day->gt(today()->addDays($limit))) {
            return [];
        }

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
            ->whereIn('status', Appointments::BLOCKING_STATUSES)
            ->when($excludedAppointmentId, fn ($query) => $query->whereKeyNot($excludedAppointmentId))
            ->where('appointment_start', '<', $day->copy()->addDay())
            ->where('appointment_end', '>', $day)
            ->get(['appointment_start', 'appointment_end']);
        $reservations = AppointmentRescheduleRequest::where('user_id', $userId)
            ->where('status', 'pending')
            ->when($excludedRescheduleRequestId, fn ($query) => $query->whereKeyNot($excludedRescheduleRequestId))
            ->where('requested_start', '<', $day->copy()->addDay())
            ->where('requested_end', '>', $day)
            ->get(['requested_start', 'requested_end']);
        $holds = AppointmentSlotHold::where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->when($excludedHoldToken, fn ($query) => $query->whereKeyNot($excludedHoldToken))
            ->where('appointment_start', '<', $day->copy()->addDay())
            ->where('appointment_end', '>', $day)
            ->get(['appointment_start', 'appointment_end']);

        $starts = $this->candidateStarts($service, $userId, $day, $workStart, $workEnd, $duration);
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
            if ($reservations->contains(fn (AppointmentRescheduleRequest $reservation) => $this->overlaps(
                $start, $end, $reservation->requested_start, $reservation->requested_end,
            ))) {
                continue;
            }
            if ($holds->contains(fn (AppointmentSlotHold $hold) => $this->overlaps(
                $start, $end, $hold->appointment_start, $hold->appointment_end,
            ))) {
                continue;
            }
            $slots[] = ['start' => $start->format('H:i'), 'end' => $end->format('H:i')];
        }

        return $this->rooms->filterAvailableSlots($userId, $date, $slots, $excludedAppointmentId, $excludedRescheduleRequestId, $excludedHoldToken);
    }

    public function containsSlot(
        string $date,
        int $serviceId,
        int $userId,
        string $start,
        string $end,
        ?int $excludedAppointmentId = null,
        ?int $excludedRescheduleRequestId = null,
        ?string $excludedHoldToken = null,
    ): bool {
        return collect($this->slots($date, $serviceId, $userId, $excludedAppointmentId, $excludedRescheduleRequestId, $excludedHoldToken))
            ->contains(fn (array $slot) => $slot['start'] === $start && $slot['end'] === $end);
    }

    private function candidateStarts(Services $service, int $userId, Carbon $day, Carbon $workStart, Carbon $workEnd, int $duration): array
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

        $rule = $service->ruleForDate($day->toDateString());
        $hasFixedTime = (bool) ($rule?->has_fixed_time ?? $service->has_fixed_time);
        $timeFrom = $rule?->time_from ?? $service->time_from;
        $timeTo = $rule?->time_to ?? $service->time_to;
        $start = $hasFixedTime && $timeFrom
            ? $this->at($day, $timeFrom)
            : $workStart->copy();
        $limit = $hasFixedTime && $timeTo
            ? $this->at($day, $timeTo)
            : $workEnd->copy();
        $starts = [];
        for ($time = $start; $time->copy()->addMinutes($duration)->lte($limit); $time->addMinutes(30)) {
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
