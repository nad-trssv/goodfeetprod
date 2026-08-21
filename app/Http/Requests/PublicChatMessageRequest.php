<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicChatMessageRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['token'=>['required','string','size:64'],'message'=>['required','string','max:4000']]; }
}
