<?php

namespace App\Http\Requests\RedDays;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string', 'min:3'],
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'required_with:start_time', 'after:start_time'],
            'repeat' => ['required', 'boolean'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'description.string' => 'The description must be a string.',
            'description.min' => 'The description must be at least 3 characters.',
            'date.required' => 'The date field is required.',
            'date.date_format' => 'The date must be in the format Y-m-d.',
            'date.unique' => 'The date has already been taken.',
            'start_time.date_format' => 'The start time must be in the format H:i.',
            'end_time.date_format' => 'The end time must be in the format H:i.',
            'end_time.required_with' => 'The end time is required when start time is present.',
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }
}
