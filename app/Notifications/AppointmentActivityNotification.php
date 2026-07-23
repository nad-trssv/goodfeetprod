<?php

namespace App\Notifications;

use App\Models\AppointmentRescheduleRequest;
use App\Models\Appointments;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Appointments $appointment,
        private readonly string $event,
        private readonly ?AppointmentRescheduleRequest $rescheduleRequest = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $appointment = $this->appointment->loadMissing(['service', 'user']);

        return [
            'event' => $this->event,
            'appointment_id' => $appointment->id,
            'appointment_uuid' => $appointment->public_uuid,
            'client_name' => trim($appointment->client_name.' '.$appointment->client_lastname),
            'service_name' => $appointment->service?->name,
            'master_name' => $appointment->user?->name,
            'appointment_start' => $appointment->appointment_start?->toIso8601String(),
            'reschedule_request_uuid' => $this->rescheduleRequest?->public_uuid,
            'requested_start' => $this->rescheduleRequest?->requested_start?->toIso8601String(),
        ];
    }
}
