<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomerDocument extends Model
{
    protected $fillable = ['public_uuid','customer_id','uploaded_by_user_id','category','original_name','disk','path','mime_type','size'];
    protected static function booted(): void { static::creating(fn (self $model) => $model->public_uuid ??= (string) Str::uuid()); }
    public function getRouteKeyName(): string { return 'public_uuid'; }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function uploadedBy() { return $this->belongsTo(User::class, 'uploaded_by_user_id'); }
}
