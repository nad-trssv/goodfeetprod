<?php

namespace App\Http\Requests;

use App\Models\Appointments;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use App\Models\Customer;
use App\Services\Localization\SiteLocaleRegistry;

class AppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        if ($this->isMethod('post')) {
            return $user->hasPermission('appointments.create')
                && ($user->hasAllAppointmentsScope() || $this->integer('user_id') === (int) $user->id);
        }

        $appointment = Appointments::find($this->integer('id'));

        return $appointment
            && $user->can('update', $appointment)
            && ($user->hasAllAppointmentsScope() || $this->integer('user_id') === (int) $user->id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', Rule::exists('appointments', 'id')],
            'title' => ['nullable', 'string', 'max:190'],
            'client_name' => ['required', 'string', 'max:190'],
            'client_lastname' => ['nullable', 'string', 'max:190'],
            'client_phone' => ['required', 'string', 'min:8', 'max:32'],
            'client_email' => ['nullable', 'email', 'max:190'],
            'client_locale' => ['nullable', 'string', Rule::in(array_keys(app(SiteLocaleRegistry::class)->activeLabels()))],
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')],
            'description' => ['nullable', 'string', 'max:2000'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where(fn ($query) => $query->where('status', true)->where('is_deleted', false))],
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'price' => ['nullable', 'numeric', 'min:0'],
            'appointment_start' => ['required', 'date'],
            'appointment_end' => ['required', 'date', 'after:appointment_start'],
            'slot_hold_token' => ['nullable', 'uuid', Rule::exists('appointment_slot_holds', 'token')],
            'repeat_count' => ['nullable', 'integer', 'min:1', 'max:12'],
            'repeat_interval_weeks' => ['nullable', 'integer', 'min:1', 'max:8'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'title.required' => __('admin_validation.appointment.title'),
            'client_name.required' => __('admin_validation.appointment.name'),
            'client_lastname.required' => __('admin_validation.appointment.lastname'),
            'client_phone.required' => __('admin_validation.appointment.phone'),
            'client_phone.regex' => __('admin_validation.appointment.phone_format'),
            'client_phone.min' => __('admin_validation.appointment.phone_min'),
            'description.min' => __('admin_validation.appointment.description_min'),
            'appointment_end.after' => __('admin_validation.appointment.end_after'),
            'service_id.required' => __('admin_validation.appointment.service_required'),
            'service_id.exists' => __('admin_validation.appointment.service_exists'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $master = \App\Models\User::find($this->integer('user_id'));
            if (! $master || ! $master->isServiceProvider() || ! $master->services()->whereKey($this->integer('service_id'))->exists()) {
                $validator->errors()->add('user_id', __('admin_appointment_create.errors.master'));
            }

            if (! $this->filled('customer_id') || $this->user()?->hasAllAppointmentsScope()) {
                return;
            }

            $canUseCustomer = Customer::whereKey($this->integer('customer_id'))
                ->whereHas('appointments', fn ($query) => $query->where('user_id', $this->user()->id))
                ->exists();

            if (! $canUseCustomer) {
                $validator->errors()->add('customer_id', __('admin_appointment_create.errors.customer_access'));
            }
        });
    }

}
