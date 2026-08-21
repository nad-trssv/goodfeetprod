<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerConsent extends Model
{
    protected $fillable = ['customer_id','type','is_granted','captured_at','withdrawn_at','source','note','recorded_by_user_id'];
    protected function casts(): array { return ['is_granted'=>'boolean','captured_at'=>'datetime','withdrawn_at'=>'datetime']; }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by_user_id'); }
}
