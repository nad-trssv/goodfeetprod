<?php

namespace App\Services\Booking;

use App\Models\AppointmentAudit;
use App\Models\Appointments;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class AppointmentAuditService
{
    public const TRACKED_FIELDS = [
        'status', 'service_id', 'user_id', 'room_id', 'appointment_start', 'appointment_end',
        'price', 'original_price', 'discount_amount', 'promo_code', 'description', 'admin_notes',
        'series_uuid', 'series_sequence', 'series_total',
    ];

    public function created(Appointments $appointment, ?Model $actor = null): AppointmentAudit
    {
        return $this->record($appointment, 'created', $actor, null, $this->snapshot($appointment));
    }

    public function updated(Appointments $appointment, array $before, ?Model $actor = null, ?string $reason = null, string $event = 'updated', array $context = []): ?AppointmentAudit
    {
        $after = $this->snapshot($appointment);
        $changes = [];
        foreach (self::TRACKED_FIELDS as $field) {
            $old = $this->normalize($before[$field] ?? null);
            $new = $after[$field] ?? null;
            if ($old !== $new) {
                $changes[$field] = ['old' => $old, 'new' => $new];
            }
        }

        return $changes === [] ? null : $this->record($appointment, $event, $actor, $reason, $changes, $context);
    }

    public function event(Appointments $appointment, string $event, ?Model $actor = null, ?string $reason = null, array $changes = [], array $context = []): AppointmentAudit
    {
        return $this->record($appointment, $event, $actor, $reason, $changes, $context);
    }

    public function snapshot(Appointments $appointment): array
    {
        return collect($appointment->only(self::TRACKED_FIELDS))
            ->map(fn ($value) => $this->normalize($value))
            ->all();
    }

    private function record(Appointments $appointment, string $event, ?Model $actor, ?string $reason, array $changes, array $context = []): AppointmentAudit
    {
        return $appointment->auditTrail()->create([
            'event' => $event,
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getKey(),
            'reason' => $reason ? mb_substr(trim($reason), 0, 500) : null,
            'changes' => $changes === [] ? null : $changes,
            'context' => $context === [] ? null : $context,
        ]);
    }

    private function normalize(mixed $value): mixed
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        if (is_float($value)) {
            return number_format($value, 2, '.', '');
        }

        return $value;
    }
}
