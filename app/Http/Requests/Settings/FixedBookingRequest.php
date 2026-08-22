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
            'fixedBooking.required'    => __('admin_validation.fixed.required'),
            'fixedBooking.array'       => __('admin_validation.fixed.array'),
            'fixedBooking.min'         => __('admin_validation.fixed.min'),
            'fixedBooking.*.required'  => __('admin_validation.fixed.item_required'),
            'fixedBooking.*.date_format' => __('admin_validation.time_format'),
        ];
    }
}
