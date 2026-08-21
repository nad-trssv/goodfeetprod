<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentSlotHold extends Model
{
    protected $primaryKey = 'token';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'token', 'service_id', 'user_id', 'room_id', 'held_by_user_id',
        'appointment_start', 'appointment_end', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_start' => 'datetime',
            'appointment_end' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
