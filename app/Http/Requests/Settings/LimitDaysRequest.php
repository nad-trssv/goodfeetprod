<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class LimitDaysRequest extends FormRequest
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
            'days' => 'required_if:active,true|integer',
            'active' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'days.integer' => 'Поле "Дни" должно быть числом',
            'days.required' => 'Поле "Дни" обязательно для заполнения',
        ];
    }
}
