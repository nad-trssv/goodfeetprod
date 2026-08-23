<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessagingTriggerSetting extends Model
{
    public const TRIGGERS = ['booking_created', 'appointment_reminder', 'review_request'];

    protected $fillable = ['trigger', 'enabled', 'timing_minutes', 'templates'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'timing_minutes' => 'integer',
            'templates' => 'array',
        ];
    }
}
