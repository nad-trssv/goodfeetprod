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
        ];
    }
    
    public function messages(): array
    {
        return [
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

}
