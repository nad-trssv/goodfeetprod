<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentStatusRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['confirmed', 'checked_in', 'in_progress', 'completed', 'no_show'])],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
