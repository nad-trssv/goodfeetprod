<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrmDocumentRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasPermission('crm.documents') === true; }
    public function rules(): array
    {
        return [
            'category'=>['required',Rule::in(['general','consent','photo','medical','other'])],
            'file'=>['required','file','mimes:pdf,jpg,jpeg,png,webp','max:10240'],
        ];
    }
}
