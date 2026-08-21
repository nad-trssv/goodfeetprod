<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PublicChatStartRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['name'=>['nullable','string','max:190'],'email'=>['nullable','email','max:190'],'phone'=>['nullable','string','max:32'],'message'=>['required','string','max:4000']]; }
    public function after(): array
    {
        return [function(Validator $validator){
            if (! auth('customer')->check() && (!filled($this->input('name')) || (!filled($this->input('email')) && !filled($this->input('phone'))))) {
                $validator->errors()->add('name', __('crm.contact_required'));
            }
        }];
    }
}
