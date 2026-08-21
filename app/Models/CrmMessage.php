<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmMessage extends Model
{
    protected $fillable = ['conversation_id','sender_type','sender_id','body'];
    public function conversation() { return $this->belongsTo(CrmConversation::class, 'conversation_id'); }
    public function staffSender() { return $this->belongsTo(User::class, 'sender_id'); }
}
