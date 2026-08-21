<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AdminSlotHoldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('appointments.create') === true;
    }

    public function rules(): array
    {
        return [
            'token' => ['nullable', 'uuid'],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')],
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'appointment_start' => ['required', 'date'],
            'appointment_end' => ['required', 'date', 'after:appointment_start'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $master = User::find($this->integer('user_id'));
            if (! $master || ! $master->isServiceProvider() || ! $master->services()->whereKey($this->integer('service_id'))->exists()) {
                $validator->errors()->add('user_id', __('admin_appointment_create.errors.master_service'));
            }
            if (! $this->user()->hasAllAppointmentsScope() && $this->integer('user_id') !== (int) $this->user()->id) {
                $validator->errors()->add('user_id', __('admin_roles.access_denied'));
            }
        }];
    }
}
