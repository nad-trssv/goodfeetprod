<?php

namespace App\Observers;

use App\Models\Appointments;
use App\Services\Messaging\TriggerScheduleService;

class AppointmentTriggerObserver
{
    public function __construct(private readonly TriggerScheduleService $scheduler) {}

    public function created(Appointments $appointment): void
    {
        $this->scheduler->sync($appointment, true);
    }

    public function updated(Appointments $appointment): void
    {
        if ($appointment->wasChanged([
            'appointment_start', 'appointment_end', 'status', 'client_locale',
            'client_phone', 'customer_id',
        ])) {
            $this->scheduler->sync($appointment);
        }
    }
}
