<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['name', 'capacity', 'description', 'is_active'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'room_user');
    }
}