<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRule extends Model
{
    protected $fillable = [
        'service_id',
        'valid_from',
        'valid_to',
        'price',
        'duration_minutes',
        'duration_minutes_min',
        'time_from',
        'time_to',
        'has_fixed_time',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to'   => 'date',
        'has_fixed_time' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Services::class, 'service_id');
    }
}
