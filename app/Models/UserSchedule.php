<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'monday_start', 'monday_end',
        'tuesday_start', 'tuesday_end',
        'wednesday_start', 'wednesday_end',
        'thursday_start', 'thursday_end',
        'friday_start', 'friday_end',
        'saturday_start', 'saturday_end',
        'sunday_start', 'sunday_end',
        'lunch_start', 'lunch_end',
        'fixed_booking_enabled',
        'fixed_booking_slots',
    ];

    protected $casts = [
        'fixed_booking_enabled' => 'boolean',
        'fixed_booking_slots' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}