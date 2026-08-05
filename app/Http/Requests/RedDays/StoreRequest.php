<?php

namespace App\Http\Requests\RedDays;

use App\Models\RedDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string', 'min:3'],
            'date' => ['required', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date'],
            'type' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(RedDay::TYPES))],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'required_with:start_time', 'after:start_time'],
            'repeat' => ['required', 'boolean'],
            'user_id' => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'description.string' => 'The description must be a string.',
            'description.min' => 'The description must be at least 3 characters.',
            'date.required' => 'The date field is required.',
            'date.date_format' => 'The date must be in the format Y-m-d.',
            'start_time.date_format' => 'The start time must be in the format H:i.',
            'end_time.date_format' => 'The end time must be in the format H:i.',
            'end_time.required_with' => 'The end time is required when start time is present.',
            'end_time.after' => 'The end time must be after the start time.',
            'user_id.exists' => 'Selected master not found.',
        ];
    }
}
