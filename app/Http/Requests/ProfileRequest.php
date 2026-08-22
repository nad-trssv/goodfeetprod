<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::in([Auth::id()])],
            'name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+[\d\s]+$/',
                Rule::unique('users', 'phone')->ignore(Auth::id()),
            ],
            'email' => ['required', 'email', 'max:55', Rule::unique('users', 'email')->ignore(Auth::id())],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^@(?!.*\.\.)(?!.*\.$)[a-zA-Z0-9._]+$/',
                Rule::unique('users', 'username')->ignore(Auth::id()),
            ],
            'profile_photo_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'professional_titles' => ['nullable', 'array'],
            'professional_titles.*' => ['nullable', 'string', 'max:120'],
            'locale' => ['sometimes', 'required', 'string', Rule::in(array_keys(config('supported_locales')))],
            'password' => [
                'required_with:oldPassword',
                'nullable',
                'min:8',
                'max:255',
            ],
            'password_confirm' => [
                'required_with:password',
                'same:password',
            ],
            'oldPassword' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    $userId = $this->input('user_id');
                    $user = \App\Models\User::find($userId);

                    if (!$user || !\Hash::check($value, $user->password)) {
                        $fail(__('admin_staff.old_password_invalid'));
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return __('admin_validation.profile');
    }
}
