<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentMedia extends Model
{
    use HasFactory;

    protected $fillable = ['appointment_id', 'photo_path'];

    public function appointment()
    {
        return $this->belongsTo(Appointments::class);
    }
}
