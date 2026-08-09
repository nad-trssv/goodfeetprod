<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminAppointmentAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->user()?->hasPermission('appointments.create')) {
            return false;
        }

        $requestedMaster = (string) $this->input('user_id');

        return $this->user()->hasAllAppointmentsScope()
            || $requestedMaster === (string) $this->user()->id;
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
                    $master = ctype_digit((string) $value) ? \App\Models\User::find($value) : null;
                    if ($value !== 'all' && (! $master || ! $master->isServiceProvider())) {
                        $fail(__('admin_appointment_create.errors.master'));
                    }
                },
            ],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ];
    }
}
