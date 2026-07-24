<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserServices extends Model
{
    use HasFactory;

    protected $table = 'user_services';

    protected $fillable = [
        'user_id',
        'service_id',
        'is_active',
        'price_override',
        'duration_minutes_override',
        'duration_minutes_min_override',
        'buffer_before_minutes',
        'buffer_after_minutes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_override' => 'decimal:2',
        'duration_minutes_override' => 'integer',
        'duration_minutes_min_override' => 'integer',
        'buffer_before_minutes' => 'integer',
        'buffer_after_minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Services::class, 'service_id');
    }

    public function effectivePrice(): float
    {
        return $this->price_override !== null
            ? (float) $this->price_override
            : (float) $this->service->price;
    }

    public function effectiveDurationMinutes(): int
    {
        return $this->duration_minutes_override !== null
            ? (int) $this->duration_minutes_override
            : (int) $this->service->duration_minutes;
    }

    public function effectiveMinimumDurationMinutes(): ?int
    {
        if ($this->duration_minutes_min_override !== null) {
            return (int) $this->duration_minutes_min_override;
        }

        return $this->service->duration_minutes_min !== null
            ? (int) $this->service->duration_minutes_min
            : null;
    }
}