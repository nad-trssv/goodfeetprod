<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'description', 
        'user_id', 
        'repeat', 
        'start_time', 
        'end_time', 
        'organized_by'
    ];

    protected $dates = [
        'date',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organized_by');
    }
    public function celebrant()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
