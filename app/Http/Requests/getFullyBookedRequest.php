<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class getFullyBookedRequest extends FormRequest
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
            'service_id'    => ['required', 'integer'],
            'user_id'       => ['required', 'integer'],
            'choose_date'   => ['nullable', 'date_format:Y-m-d'],
            'range_start'   => ['nullable', 'date_format:Y-m-d'],
            'range_end'     => ['nullable', 'date_format:Y-m-d', 'after_or_equal:range_start'],
        ];
    }
}
