<?php

namespace App\Policies;

use App\Models\Appointments;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('appointments.view');
    }

    public function view(User $user, Appointments $appointment): bool
    {
        return $this->withinScope($user, $appointment) && $user->hasPermission('appointments.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('appointments.create');
    }

    public function update(User $user, Appointments $appointment): bool
    {
        return $this->withinScope($user, $appointment) && $user->hasPermission('appointments.update');
    }

    public function delete(User $user, Appointments $appointment): bool
    {
        return $this->withinScope($user, $appointment) && $user->hasPermission('appointments.delete');
    }

    public function status(User $user, Appointments $appointment): bool
    {
        return $this->withinScope($user, $appointment) && $user->hasPermission('appointments.status');
    }

    public function message(User $user, Appointments $appointment): bool
    {
        return $this->withinScope($user, $appointment) && $user->hasPermission('appointments.message');
    }

    private function withinScope(User $user, Appointments $appointment): bool
    {
        return $user->hasAllAppointmentsScope() || (int) $appointment->user_id === (int) $user->id;
    }
}
