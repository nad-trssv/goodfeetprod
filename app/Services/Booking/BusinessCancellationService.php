<?php

namespace App\Services\Booking;

use App\Models\Appointments;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class BusinessCancellationService
{
    public function __construct(private readonly AppointmentStatusService $statuses) {}

    public function cancel(Appointments $appointment, User $actor, string $reason): Appointments
    {
        if ($actor->role_id !== 1 && $appointment->user_id !== $actor->id) {
            throw new AuthorizationException('You may only cancel your own appointments.');
        }

        return $this->statuses->transition($appointment, 'cancelled_by_business', $actor, $reason);
    }
}
