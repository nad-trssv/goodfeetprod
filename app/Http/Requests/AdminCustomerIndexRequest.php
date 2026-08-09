<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminCustomerIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['search' => trim((string) $this->input('search')) ?: null]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'master_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'sort' => ['nullable', Rule::in(['recent', 'name', 'appointments'])],
        ];
    }
}
