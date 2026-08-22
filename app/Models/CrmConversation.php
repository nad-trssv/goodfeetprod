<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CrmConversation extends Model
{
    protected $fillable = ['public_uuid','access_token_hash','customer_id','previous_conversation_id','visitor_name','visitor_email','visitor_phone','status','assigned_to_user_id','last_message_at','closed_at'];
    protected $hidden = ['access_token_hash'];
    protected function casts(): array { return ['last_message_at'=>'datetime','closed_at'=>'datetime']; }
    protected static function booted(): void { static::creating(fn (self $model) => $model->public_uuid ??= (string) Str::uuid()); }
    public function getRouteKeyName(): string { return 'public_uuid'; }
    public function messages() { return $this->hasMany(CrmMessage::class, 'conversation_id'); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to_user_id'); }
    public function reads() { return $this->hasMany(CrmConversationRead::class, 'conversation_id'); }
    public function rating() { return $this->hasOne(CrmConversationRating::class, 'conversation_id'); }
    public function previousConversation() { return $this->belongsTo(self::class, 'previous_conversation_id'); }
    public function nextConversation() { return $this->hasOne(self::class, 'previous_conversation_id'); }
}
