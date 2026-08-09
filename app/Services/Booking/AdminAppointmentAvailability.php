<?php

namespace App\Services\Booking;

use App\Models\Services;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdminAppointmentAvailability
{
    public function __construct(
        private readonly SlotAvailabilityService $availability,
        private readonly BookingCalendarService $calendar,
    ) {
    }

    public function get(Services $service, string $date, int|string $masterPreference): array
    {
        $masters = $service->users()
            ->with('role')
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.profile_photo_path', 'users.role_id'])
            ->filter->isServiceProvider()
            ->values();

        if ($masterPreference !== 'all') {
            $masters = $masters->where('id', (int) $masterPreference)->values();
        }

        abort_if($masters->isEmpty(), 422, __('admin_appointment_create.errors.master_service'));

        $loadedDates = [];
        $selectedSlots = $this->slotsForDate($date, $service->id, $masters);
        $loadedDates[$date] = $selectedSlots;
        $recommendations = collect($selectedSlots)->take(6);

        $cursor = Carbon::parse($date);
        $limitDate = today()->addDays(max(0, (int) ($this->calendar->bookingLimit()['days'] ?? 30)));
        for ($offset = 1; $recommendations->count() < 6 && $offset <= 7; $offset++) {
            $candidate = $cursor->copy()->addDays($offset);
            if ($candidate->gt($limitDate)) {
                break;
            }
            $candidateDate = $candidate->toDateString();
            $loadedDates[$candidateDate] = $this->slotsForDate($candidateDate, $service->id, $masters);
            $recommendations = $recommendations->concat($loadedDates[$candidateDate])->take(6);
        }

        $calendarDays = collect(range(0, 13))->map(function (int $offset) use ($cursor, $loadedDates) {
            $day = $cursor->copy()->addDays($offset);
            $date = $day->toDateString();

            return [
                'date' => $date,
                'weekday' => mb_strtoupper(mb_substr($day->translatedFormat('D'), 0, 2)),
                'day' => $day->format('d'),
                'month' => $day->translatedFormat('M'),
                'available' => array_key_exists($date, $loadedDates) ? count($loadedDates[$date]) > 0 : null,
            ];
        });

        return [
            'date' => $date,
            'slots' => $selectedSlots,
            'recommendations' => $recommendations->values(),
            'calendar_days' => $calendarDays,
            'price' => $service->effectivePriceForDate($date),
            'duration_minutes' => $this->calendar->serviceDuration($service->id, $date),
        ];
    }

    private function slotsForDate(string $date, int $serviceId, Collection $masters): array
    {
        return $masters->flatMap(function ($master) use ($date, $serviceId) {
            return collect($this->availability->slots($date, $serviceId, $master->id))->map(fn (array $slot) => [
                'date' => $date,
                'start' => $slot['start'],
                'end' => $slot['end'],
                'user_id' => $master->id,
                'master_name' => $master->name,
                'master_photo_url' => $master->profile_photo_url,
            ]);
        })->sortBy(fn (array $slot) => $slot['date'].' '.$slot['start'].' '.str_pad((string) $slot['user_id'], 10, '0', STR_PAD_LEFT))
            ->values()
            ->all();
    }
}
