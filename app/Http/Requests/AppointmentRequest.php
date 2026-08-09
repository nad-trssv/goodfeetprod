<?php

namespace App\Http\Requests;

use App\Models\Appointments;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use App\Models\Customer;

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
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')],
            'description' => ['nullable', 'string', 'max:2000'],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where(fn ($query) => $query->where('status', true)->where('is_deleted', false))],
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'price' => ['nullable', 'numeric', 'min:0'],
            'appointment_start' => ['required', 'date'],
            'appointment_end' => ['required', 'date', 'after:appointment_start'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'title.required' => 'Пожалуйста, укажите Заголовок.',
            'client_name.required' => 'Пожалуйста, укажите Имя.',
            'client_lastname.required' => 'Пожалуйста, укажите Фамилию.',
            'client_phone.required' => 'Пожалуйста, укажите номер телефона.',
            'client_phone.regex' => 'Номер телефона должен быть в правильном формате, например: +372 55555555.',
            'client_phone.min' => 'Номер телефона должен содержать не менее 8 символов.',
            'description.min' => 'Описание должно содержать не менее 8 символов.',
            'appointment_end.after' => 'Время завершения должно быть позже времени начала.',
            'service_id.required' => 'Пожалуйста, укажите услугу.',
            'service_id.exists' => 'Выберите услугу из списка.',
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
