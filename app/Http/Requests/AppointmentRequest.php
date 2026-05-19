<?php

namespace App\Http\Requests;

use App\Models\Appointments;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentRequest extends FormRequest
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
            'title' => ['nullable', 'string', 'max:190'],
            'client_name' => ['required', 'string', 'max:190'],
            'client_lastname' => ['required', 'string', 'max:190'],
            'client_phone' => ['required', 'string', 'min:8'],
            'description' => ['nullable', 'string', 'min:8'],
            'service_id' => ['required', 'string', Rule::exists('services', 'id')],
            'user_id' => ['required', 'string', Rule::exists('users', 'id')],
            'price' => ['required'],
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

}
