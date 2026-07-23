<?php

namespace App\Services\Booking;

use App\Models\AppointmentRescheduleRequest;
use App\Models\Appointments;
use App\Models\User;
use App\Notifications\AppointmentActivityNotification;
use Illuminate\Support\Facades\Notification;

class AppointmentNotificationService
{
    public function send(Appointments $appointment, string $event, ?AppointmentRescheduleRequest $request = null): void
    {
        $recipientIds = User::query()->where('role_id', 1)->pluck('id')
            ->push($appointment->user_id)
            ->merge(
                User::whereKey($appointment->user_id)
                    ->first()?->notificationRecipients()
                    ->pluck('recipient_id') ?? collect()
            )
            ->filter()
            ->unique()
            ->values();

        $recipients = User::whereIn('id', $recipientIds)->get();
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new AppointmentActivityNotification($appointment, $event, $request));
        }
    }
}
