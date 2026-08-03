<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromoCodeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge(['code' => mb_strtoupper(trim((string) $this->code))]);
    }

    public function rules(): array
    {
        $promo = $this->route('promoCode');
        return [
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('promo_codes', 'code')->ignore($promo?->id)],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'gt:0', Rule::when($this->discount_type === 'percentage', ['max:100'])],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_customer_limit' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after:valid_from'],
            'first_booking_only' => ['required', 'boolean'],
            'active' => ['required', 'boolean'],
            'services' => ['nullable', 'array'],
            'services.*' => ['integer', 'distinct', Rule::exists('services', 'id')->where('is_deleted', 0)],
        ];
    }
}
