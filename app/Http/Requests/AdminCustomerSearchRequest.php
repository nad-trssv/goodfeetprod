<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminCustomerSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('customers.view') === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['query' => trim((string) $this->input('query'))]);
    }

    public function rules(): array
    {
        return ['query' => ['required', 'string', 'min:2', 'max:100']];
    }
}
