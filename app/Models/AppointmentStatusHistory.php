<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentStatusHistory extends Model
{
    protected $fillable = ['appointment_id', 'from_status', 'to_status', 'changed_by_type', 'changed_by_id', 'reason'];

    public function appointment()
    {
        return $this->belongsTo(Appointments::class);
    }

    public function changedBy()
    {
        return $this->morphTo();
    }
}
