<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrmNoteRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasPermission('crm.update') === true; }
    public function rules(): array { return ['body'=>['required','string','max:10000'],'is_pinned'=>['nullable','boolean']]; }
}
