<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrmConsentRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasPermission('crm.update') === true; }
    public function rules(): array
    {
        return [
            'type'=>['required',Rule::in(['data_processing','marketing','photos','health_data','terms'])],
            'is_granted'=>['required','boolean'],'captured_at'=>['required','date','before_or_equal:now'],
            'source'=>['nullable','string','max:80'],'note'=>['nullable','string','max:2000'],
        ];
    }
}
