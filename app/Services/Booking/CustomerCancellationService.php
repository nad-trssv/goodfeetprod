<?php

namespace App\Services\Booking;

use App\Models\Appointments;
use App\Models\Customer;
use App\Models\SiteSettings;
use Illuminate\Validation\ValidationException;

class CustomerCancellationService
{
    public function __construct(private readonly AppointmentStatusService $statuses) {}

    public function noticeHours(): int
    {
        $payload = SiteSettings::where('key', 'cancellation_notice_hours')->value('payload');
        return max(0, (int) ($payload ? json_decode($payload, true) : 24));
    }

    public function enabled(): bool
    {
        $payload = SiteSettings::where('key', 'allow_customer_cancellation')->value('payload');

        return $payload === null || (bool) json_decode($payload, true);
    }

    public function cancel(Appointments $appointment, Customer $customer, ?string $reason = null): Appointments
    {
        if (! $this->enabled()) {
            throw ValidationException::withMessages(['appointment' => __('customer.cancellation_disabled')]);
        }
        if ($appointment->customer_id !== $customer->id) {
            abort(404);
        }
        if (! in_array($appointment->status, ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages(['appointment' => __('customer.cancel_not_available')]);
        }
        if ($appointment->rescheduleRequests()->where('status', 'pending')->exists()) {
            throw ValidationException::withMessages(['appointment' => __('customer.reschedule_already_pending')]);
        }
        if (now()->gte($appointment->appointment_start->copy()->subHours($this->noticeHours()))) {
            throw ValidationException::withMessages(['appointment' => __('customer.cancel_too_late', ['hours' => $this->noticeHours()])]);
        }

        return $this->statuses->transition($appointment, 'cancelled_by_client', $customer, $reason);
    }
}
