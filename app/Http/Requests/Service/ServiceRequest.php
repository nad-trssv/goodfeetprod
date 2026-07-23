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
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'masters' => 'required|array',
            'masters.*' => 'integer|exists:users,id',
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
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
