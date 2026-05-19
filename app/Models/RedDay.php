<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'start_time',
        'end_time',
        'full_day',
        'description', 
        'repeat'
    ];

    protected $dates = [
        'date',
    ];
}
