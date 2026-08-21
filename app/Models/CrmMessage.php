<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmMessage extends Model
{
    protected $fillable = ['conversation_id','sender_type','event_type','sender_id','metadata','is_public','body'];
    protected function casts(): array { return ['metadata'=>'array','is_public'=>'boolean']; }
    public function conversation() { return $this->belongsTo(CrmConversation::class, 'conversation_id'); }
    public function staffSender() { return $this->belongsTo(User::class, 'sender_id'); }
}
