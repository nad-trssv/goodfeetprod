<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCodeRedemption extends Model
{
    protected $fillable = ['promo_code_id', 'appointment_id', 'customer_id', 'customer_email', 'original_price', 'discount_amount'];
    protected function casts(): array { return ['original_price' => 'decimal:2', 'discount_amount' => 'decimal:2']; }
    public function promoCode() { return $this->belongsTo(PromoCode::class); }
    public function appointment() { return $this->belongsTo(Appointments::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
}
