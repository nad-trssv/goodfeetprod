<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicChatRatingRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'token'=>['required','string','size:64'],
            'rating'=>['required','integer','between:1,5'],
        ];
    }
}
