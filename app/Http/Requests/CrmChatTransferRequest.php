<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrmChatTransferRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasPermission('crm.chat.reply')===true; }
    public function rules(): array { return ['user_id'=>['required','integer',Rule::exists('users','id')]]; }
}
