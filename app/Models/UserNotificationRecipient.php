<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationRecipient extends Model
{
    protected $fillable = ['master_id', 'recipient_id'];

    public function master()
    {
        return $this->belongsTo(User::class, 'master_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
