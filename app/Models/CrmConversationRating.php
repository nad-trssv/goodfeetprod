<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmConversationRating extends Model
{
    protected $fillable = ['conversation_id','staff_user_id','staff_name','rating'];
    protected function casts(): array { return ['rating'=>'integer']; }
    public function conversation() { return $this->belongsTo(CrmConversation::class); }
    public function staff() { return $this->belongsTo(User::class, 'staff_user_id'); }
}
