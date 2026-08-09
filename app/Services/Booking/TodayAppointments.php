<?php

namespace App\Services\Booking;

use App\Models\Appointments;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class TodayAppointments
{
    public function forUser(User $user, ?CarbonInterface $day = null): Collection
    {
        $day ??= now();

        return Appointments::query()
            ->with(['service', 'user', 'room'])
            ->when(! $user->hasAllAppointmentsScope(), fn ($query) => $query->where('user_id', $user->id))
            ->whereDate('appointment_start', $day->toDateString())
            ->orderBy('appointment_start')
            ->get();
    }
}
