<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'price' => ['required', 'integer'],
            'duration_minutes' => ['required', 'integer'],
            'masters' => 'required|array',
            'masters.*' => 'integer|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'masters.required' => 'Пожалуйста, выберите хотя бы одного мастера.',
            'masters.array' => 'Неверный формат данных для мастеров.',
            'masters.*.exists' => 'Выбранный мастер не существует.',
        ];
    }
    
    public function getMasters()
    {
        return $this->input('masters');
    }
}
