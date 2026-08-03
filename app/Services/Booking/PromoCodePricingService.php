<?php

namespace App\Services\Booking;

use App\Models\Appointments;
use App\Models\Customer;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use App\Models\Services;
use App\Services\Customer\CustomerIdentityService;
use Illuminate\Validation\ValidationException;

class PromoCodePricingService
{
    public function __construct(private readonly CustomerIdentityService $identities) {}

    public function quote(Services $service, string $date, ?string $code, ?Customer $customer, ?string $email, bool $lock = false): array
    {
        $originalCents = $this->cents($service->effectivePriceForDate($date));
        if (! filled($code)) return $this->result($originalCents);

        $normalized = mb_strtoupper(trim($code));
        $query = PromoCode::where('code', $normalized);
        if ($lock) $query->lockForUpdate();
        $promo = $query->first();
        if (! $promo || ! $promo->active) $this->invalid('promo.invalid');
        if ($promo->valid_from && now()->lt($promo->valid_from)) $this->invalid('promo.not_started');
        if ($promo->valid_until && now()->gt($promo->valid_until)) $this->invalid('promo.expired');
        if ($promo->minimum_amount !== null && $originalCents < $this->cents($promo->minimum_amount)) $this->invalid('promo.minimum_amount_error', ['amount' => $promo->minimum_amount]);
        if ($promo->services()->exists() && ! $promo->services()->whereKey($service->id)->exists()) $this->invalid('promo.service_not_allowed');

        $email = $email ? $this->identities->normalizeEmail($email) : null;
        if (($promo->per_customer_limit !== null || $promo->first_booking_only) && ! $customer && ! $email) $this->invalid('promo.identity_required');
        if ($promo->usage_limit !== null && $promo->redemptions()->count() >= $promo->usage_limit) $this->invalid('promo.usage_limit_error');
        if ($promo->per_customer_limit !== null && $this->customerRedemptions($promo, $customer, $email) >= $promo->per_customer_limit) $this->invalid('promo.customer_limit');
        if ($promo->first_booking_only && $this->hasPriorBooking($customer, $email)) $this->invalid('promo.first_booking_only_error');

        $discountCents = $promo->discount_type === 'percentage'
            ? (int) round($originalCents * min(10000, $this->cents($promo->discount_value)) / 10000)
            : $this->cents($promo->discount_value);
        $discountCents = min($originalCents, max(0, $discountCents));

        return $this->result($originalCents, $discountCents, $promo);
    }

    public function redeem(PromoCode $promo, Appointments $appointment, ?Customer $customer, ?string $email, float $original, float $discount): void
    {
        PromoCodeRedemption::create([
            'promo_code_id' => $promo->id, 'appointment_id' => $appointment->id,
            'customer_id' => $customer?->id,
            'customer_email' => $email ? $this->identities->normalizeEmail($email) : null,
            'original_price' => $original, 'discount_amount' => $discount,
        ]);
    }

    private function customerRedemptions(PromoCode $promo, ?Customer $customer, ?string $email): int
    {
        return $promo->redemptions()->where(function ($query) use ($customer, $email) {
            if ($customer) $query->where('customer_id', $customer->id);
            if ($email) $query->{$customer ? 'orWhere' : 'where'}('customer_email', $email);
        })->count();
    }

    private function hasPriorBooking(?Customer $customer, ?string $email): bool
    {
        return Appointments::where(function ($query) use ($customer, $email) {
            if ($customer) $query->where('customer_id', $customer->id);
            if ($email) $query->{$customer ? 'orWhere' : 'whereRaw'}($customer ? 'client_email' : 'LOWER(TRIM(client_email)) = ?', $customer ? $email : [$email]);
        })->exists();
    }

    private function result(int $original, int $discount = 0, ?PromoCode $promo = null): array
    {
        return ['original_price' => $original / 100, 'discount_amount' => $discount / 100, 'final_price' => ($original - $discount) / 100, 'promo' => $promo];
    }

    private function cents(float|string $amount): int { return (int) round((float) $amount * 100); }
    private function invalid(string $key, array $replace = []): never { throw ValidationException::withMessages(['promo_code' => __($key, $replace)]); }
}
