<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Appointments extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'confirmed', 'checked_in', 'in_progress', 'completed', 'cancelled_by_client', 'cancelled_by_business', 'no_show', 'rescheduled'];
    public const BLOCKING_STATUSES = ['pending', 'confirmed', 'checked_in', 'in_progress'];

    protected $fillable = ['public_uuid', 'customer_id', 'customer_identity_verified', 'status', 'status_changed_at', 'title', 'client_name', 'client_lastname', 'client_phone', 'client_email', 'service_id', 'promo_code_id', 'promo_code', 'user_id', 'room_id', 'appointment_start', 'appointment_end', 'description', 'admin_notes', 'series_uuid', 'series_sequence', 'series_total', 'price', 'original_price', 'discount_amount'];

    protected function casts(): array
    {
        return [
            'appointment_start' => 'datetime',
            'appointment_end' => 'datetime',
            'status_changed_at' => 'datetime',
            'customer_identity_verified' => 'boolean',
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Appointments $appointment) {
            $appointment->public_uuid ??= (string) Str::uuid();
            $appointment->status ??= 'confirmed';
            $appointment->status_changed_at ??= now();
        });
        static::saving(function (Appointments $appointment) {
            $appointment->slot_lock_key = in_array($appointment->status ?? 'confirmed', self::BLOCKING_STATUSES, true) && $appointment->user_id && $appointment->appointment_start
                ? $appointment->user_id.'|'.Carbon::parse($appointment->appointment_start)->format('Y-m-d H:i:s')
                : null;
        });

        static::created(fn (Appointments $appointment) => $appointment->statusHistory()->create([
            'from_status' => null,
            'to_status' => $appointment->status,
        ]));

        static::updated(function (Appointments $appointment) {
            if ($appointment->wasChanged('status')) {
                $appointment->statusHistory()->create([
                    'from_status' => $appointment->getOriginal('status'),
                    'to_status' => $appointment->status,
                ]);
            }
        });
    }

    public function service()
    {
        return $this->belongsTo(Services::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function promoCodeRedemption()
    {
        return $this->hasOne(PromoCodeRedemption::class, 'appointment_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(AppointmentStatusHistory::class, 'appointment_id');
    }

    public function auditTrail()
    {
        return $this->hasMany(AppointmentAudit::class, 'appointment_id');
    }

    public function media()
    {
        return $this->hasMany(AppointmentMedia::class, 'appointment_id');
    }

    public function rescheduleRequests()
    {
        return $this->hasMany(AppointmentRescheduleRequest::class, 'appointment_id');
    }
}
