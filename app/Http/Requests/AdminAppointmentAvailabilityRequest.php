<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminAppointmentAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array((int) $this->user()?->role_id, [1, 2], true);
    }

    public function rules(): array
    {
        return [
            'service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')->where(fn ($query) => $query
                    ->where('status', true)
                    ->where('is_deleted', false)),
            ],
            'user_id' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value !== 'all' && (! ctype_digit((string) $value) || ! \App\Models\User::whereKey($value)->exists())) {
                        $fail(__('admin_appointment_create.errors.master'));
                    }
                },
            ],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ];
    }
}
