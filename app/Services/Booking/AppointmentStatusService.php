<?php

namespace App\Services\Booking;

use App\Models\Appointments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentStatusService
{
    public function __construct(private readonly AppointmentNotificationService $notifications) {}

    private const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled_by_client', 'cancelled_by_business'],
        'confirmed' => ['checked_in', 'cancelled_by_client', 'cancelled_by_business', 'no_show', 'rescheduled'],
        'checked_in' => ['in_progress', 'completed'],
        'in_progress' => ['completed'],
        'completed' => [],
        'cancelled_by_client' => [],
        'cancelled_by_business' => [],
        'no_show' => [],
        'rescheduled' => [],
    ];

    public function transition(Appointments $appointment, string $toStatus, ?Model $actor = null, ?string $reason = null): Appointments
    {
        $appointment = DB::transaction(function () use ($appointment, $toStatus, $actor, $reason) {
            $appointment = Appointments::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            if (! in_array($toStatus, self::TRANSITIONS[$appointment->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => "Переход {$appointment->status} → {$toStatus} запрещён."]);
            }

            $appointment->update(['status' => $toStatus, 'status_changed_at' => now()]);
            $history = $appointment->statusHistory()->latest('id')->first();
            $history?->update([
                'changed_by_type' => $actor?->getMorphClass(),
                'changed_by_id' => $actor?->getKey(),
                'reason' => $reason ? mb_substr(trim($reason), 0, 500) : null,
            ]);

            return $appointment->refresh();
        });

        if (in_array($toStatus, ['cancelled_by_client', 'cancelled_by_business'], true)) {
            $this->notifications->send($appointment, $toStatus);
        }

        return $appointment;
    }
}
