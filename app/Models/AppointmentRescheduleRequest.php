<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AppointmentRescheduleRequest extends Model
{
    protected $fillable = ['public_uuid','appointment_id','customer_id','user_id','service_id','room_id','old_start','old_end','requested_start','requested_end','status','reason','reviewed_by','reviewed_at'];
    protected function casts(): array { return ['old_start'=>'datetime','old_end'=>'datetime','requested_start'=>'datetime','requested_end'=>'datetime','reviewed_at'=>'datetime']; }
    protected static function booted(): void { static::creating(fn ($request) => $request->public_uuid ??= (string) Str::uuid()); }
    public function appointment() { return $this->belongsTo(Appointments::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function service() { return $this->belongsTo(Services::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
