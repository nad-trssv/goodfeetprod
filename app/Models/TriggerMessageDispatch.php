<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TriggerMessageDispatch extends Model
{
    protected $fillable = [
        'appointment_id', 'trigger', 'status', 'scheduled_at', 'expires_at', 'sent_provider',
        'sent_at', 'delivered_at', 'last_error',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'expires_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function appointment()
    {
        return $this->belongsTo(Appointments::class, 'appointment_id');
    }

    public function attempts()
    {
        return $this->hasMany(TriggerMessageAttempt::class, 'dispatch_id');
    }
}
