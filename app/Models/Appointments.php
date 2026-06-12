<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointments extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'client_name', 'client_lastname', 'client_phone', 'client_email', 'service_id', 'user_id', 'room_id', 'appointment_start', 'appointment_end', 'description', 'price'];

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

    public function media()
    {
        return $this->hasMany(AppointmentMedia::class, 'appointment_id');
    }
}
