<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentFeedback extends Model
{
    protected $table = 'appointment_feedback';

    protected $fillable = ['appointment_id', 'token', 'rating', 'submitted_at', 'ip_hash'];

    protected function casts(): array
    {
        return ['rating' => 'integer', 'submitted_at' => 'datetime'];
    }

    public function appointment()
    {
        return $this->belongsTo(Appointments::class, 'appointment_id');
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }
}
