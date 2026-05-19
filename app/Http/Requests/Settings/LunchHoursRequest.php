<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class LunchHoursRequest extends FormRequest
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
            'lunch_start' => ['nullable', 'date_format:H:i'],
            'lunch_end' => ['nullable', 'date_format:H:i', 'after:lunch_start'],
        ];
    }
    public function messages()
    {
        return [
            'lunch_end.after' => 'Время окончания должно быть позже времени начала.',
            'lunch_start.date_format' => 'Поле должно быть в формате ЧЧ:ММ.',
            'lunch_end.date_format' => 'Поле должно быть в формате ЧЧ:ММ.',
        ];
    }
}
