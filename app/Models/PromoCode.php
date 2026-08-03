<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = ['code', 'discount_type', 'discount_value', 'minimum_amount', 'usage_limit', 'per_customer_limit', 'valid_from', 'valid_until', 'first_booking_only', 'active'];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2', 'minimum_amount' => 'decimal:2',
            'valid_from' => 'datetime', 'valid_until' => 'datetime',
            'first_booking_only' => 'boolean', 'active' => 'boolean',
        ];
    }

    public function services() { return $this->belongsToMany(Services::class, 'promo_code_service', 'promo_code_id', 'service_id'); }
    public function redemptions() { return $this->hasMany(PromoCodeRedemption::class); }
}
