<?php

namespace App\Services\Booking;

use App\Models\Appointments;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Collection;

class RoomAllocationService
{
    public function hasCapacity(int $userId, string $date, string $slotStart, string $slotEnd): bool
    {
        $rooms = $this->eligibleRooms($userId);
        if ($rooms->isEmpty()) {
            return ! User::whereKey($userId)->whereHas('rooms')->exists();
        }

        [$start, $end] = $this->dateTimes($date, $slotStart, $slotEnd);

        return $rooms->contains(fn (Room $room) => $this->load($room, $start, $end) < $room->capacity);
    }

    /**
     * Filter a day's candidate slots using a single room lookup and one
     * appointment lookup per eligible room. This deliberately does not cache:
     * every call observes the current schedules, room assignments and bookings.
     */
    public function filterAvailableSlots(int $userId, string $date, array $slots): array
    {
        if ($slots === []) {
            return [];
        }

        $rooms = $this->eligibleRooms($userId);
        if ($rooms->isEmpty()) {
            return User::whereKey($userId)->whereHas('rooms')->exists() ? [] : $slots;
        }

        $dayStart = "{$date} 00:00:00";
        $dayEnd = "{$date} 23:59:59";
        $roomAppointments = $rooms->mapWithKeys(function (Room $room) use ($dayStart, $dayEnd) {
            $appointments = Appointments::query()
                ->where('appointment_start', '<', $dayEnd)
                ->where('appointment_end', '>', $dayStart)
                ->where(function ($query) use ($room) {
                    $query->where('room_id', $room->id)
                        ->orWhere(function ($legacy) use ($room) {
                            $legacy->whereNull('room_id')
                                ->whereHas('user.rooms', fn ($rooms) => $rooms->whereKey($room->id));
                        });
                })
                ->get(['appointment_start', 'appointment_end']);

            return [$room->id => $appointments];
        });

        return array_values(array_filter($slots, function (array $slot) use ($date, $rooms, $roomAppointments) {
            [$start, $end] = $this->dateTimes($date, $slot['start'], $slot['end']);

            return $rooms->contains(function (Room $room) use ($start, $end, $roomAppointments) {
                $load = $roomAppointments->get($room->id)->filter(
                    fn (Appointments $appointment) => $appointment->appointment_start < $end
                        && $appointment->appointment_end > $start
                )->count();

                return $load < $room->capacity;
            });
        }));
    }

    public function assign(int $userId, string $date, string $slotStart, string $slotEnd, ?int $excludedAppointmentId = null): ?int
    {
        $rooms = $this->eligibleRooms($userId, true);
        if ($rooms->isEmpty()) {
            return null;
        }

        [$start, $end] = $this->dateTimes($date, $slotStart, $slotEnd);

        return $rooms
            ->map(fn (Room $room) => ['room' => $room, 'load' => $this->load($room, $start, $end, $excludedAppointmentId)])
            ->filter(fn (array $candidate) => $candidate['load'] < $candidate['room']->capacity)
            ->sortBy(fn (array $candidate) => [$candidate['load'], $candidate['room']->id])
            ->first()['room']->id ?? null;
    }

    private function eligibleRooms(int $userId, bool $lock = false): Collection
    {
        $query = Room::query()
            ->where('is_active', true)
            ->whereHas('users', fn ($users) => $users->whereKey($userId))
            ->orderBy('id');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get();
    }

    private function load(Room $room, string $start, string $end, ?int $excludedAppointmentId = null): int
    {
        return Appointments::query()
            ->when($excludedAppointmentId, fn ($query) => $query->whereKeyNot($excludedAppointmentId))
            ->where('appointment_start', '<', $end)
            ->where('appointment_end', '>', $start)
            ->where(function ($query) use ($room) {
                $query->where('room_id', $room->id)
                    ->orWhere(function ($legacy) use ($room) {
                        $legacy->whereNull('room_id')
                            ->whereHas('user.rooms', fn ($rooms) => $rooms->whereKey($room->id));
                    });
            })
            ->count();
    }

    private function dateTimes(string $date, string $start, string $end): array
    {
        return ["{$date} {$start}:00", "{$date} {$end}:00"];
    }
}
