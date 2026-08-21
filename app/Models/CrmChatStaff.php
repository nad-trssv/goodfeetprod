<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmChatStaff extends Model
{
    protected $table = 'crm_chat_staff';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $fillable = ['user_id','is_enabled','can_view_history','must_answer'];
    protected function casts(): array { return ['is_enabled'=>'boolean','can_view_history'=>'boolean','must_answer'=>'boolean']; }
    public function user() { return $this->belongsTo(User::class); }
}
