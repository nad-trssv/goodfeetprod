<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrmChatReplyRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasPermission('crm.chat.reply')===true; }
    public function rules(): array { return ['message'=>['required','string','max:4000']]; }
}
