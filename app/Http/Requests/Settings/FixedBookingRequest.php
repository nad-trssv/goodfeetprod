<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class FixedBookingRequest extends FormRequest
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
    public function rules()
    {
        return [
            'fixedBookingStatus'    => ['required', 'boolean'],
            'fixedBooking'          => ['required', 'array', 'min:1'],
            'fixedBooking.*'        => ['required', 'date_format:H:i'], 
        ];
    }

    public function messages()
    {
        return [
            'fixedBooking.required'    => 'Вы должны указать хотя бы одно время.',
            'fixedBooking.array'       => 'Формат данных должен быть массивом.',
            'fixedBooking.min'         => 'Нужно хотя бы одно время.',
            'fixedBooking.*.required'  => 'Время не может быть пустым.',
            'fixedBooking.*.string'    => 'Каждое время должно быть строкой.',
            'fixedBooking.*.regex'     => 'Время должно быть в формате HH:MM.',
        ];
    }
}
