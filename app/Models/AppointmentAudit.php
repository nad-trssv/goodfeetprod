<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentAudit extends Model
{
    protected $fillable = ['appointment_id', 'event', 'actor_type', 'actor_id', 'reason', 'changes', 'context'];

    protected function casts(): array
    {
        return ['changes' => 'array', 'context' => 'array'];
    }

    public function appointment()
    {
        return $this->belongsTo(Appointments::class);
    }

    public function actor()
    {
        return $this->morphTo();
    }
}
