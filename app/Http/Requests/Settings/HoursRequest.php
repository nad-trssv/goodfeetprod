<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class HoursRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    private $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $rules = [];
            
        foreach ($this->days as $day) {
            $rules["{$day}_start"] = ['nullable', 'date_format:H:i'];
            $rules["{$day}_end"] = ['nullable', 'date_format:H:i', "after:{$day}_start"];
        }
        return $rules;
    }
    public function messages()
{
    $messages = [];

    foreach ($this->days as $day) {
        $messages["{$day}_start.date_format"] = __('admin_validation.time_format');
        $messages["{$day}_end.date_format"] = __('admin_validation.time_format');
        $messages["{$day}_end.after"] = __('admin_validation.end_after_start');
    }

    return $messages;
}
}
