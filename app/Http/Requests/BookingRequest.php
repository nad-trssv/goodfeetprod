<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    
    public function rules(): array
    {
        return [
            'client_name' => ['required', 'string', 'max:190'],
            'client_lastname' => ['required', 'string', 'max:190'],
            'client_phone' => ['required', 'string', 'min:8'],
            'client_email' => ['required', 'string', 'email', 'max:190'],
            'description' => ['nullable', 'string', 'min:8'],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where(fn ($query) => $query->where('status', 1)->where('is_deleted', 0))],
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'choose_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'appointment_start' => ['required', 'date_format:H:i'],
            'appointment_end' => ['required', 'date_format:H:i', 'after:appointment_start'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'mimes:jpg,jpeg,png'],
            'policy' => ['required', 'accepted'],
            'promo_code' => ['nullable', 'string', 'max:50', 'regex:/^[\pL\pN_-]+$/u'],
        ];
    }
    
    public function messages(): array
    {
        return [
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

}
