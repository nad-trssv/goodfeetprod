<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'string', Rule::exists('users', 'id')],
            'name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+[\d\s]+$/',
            ],
            'email' => 'required|email|max:55',
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^@(?!.*\.\.)(?!.*\.$)[a-zA-Z0-9._]+$/',
            ],
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
                        $fail('Поле "Старый пароль" неверно.');
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
        return [
            'name.required' => 'Поле "Имя" обязательно для заполнения.',
            'name.string' => 'Поле "Имя" должно быть строкой.',
            'name.max' => 'Поле "Имя" не должно превышать 255 символов.',
            'phone.required' => 'Поле "Телефон" обязательно для заполнения.',
            'phone.string' => 'Поле "Телефон" должно быть строкой.',
            'phone.max' => 'Поле "Телефон" не должно превышать 15 символов.',
            'email.required' => 'Поле "Email" обязательно для заполнения.',
            'email.email' => 'Поле "Email" должно быть действительным адресом электронной почты.',
            'email.max' => 'Поле "Email" не должно превышать 25 символов.',
            'username.required' => 'Поле "Имя пользователя" обязательно для заполнения.',
            'username.string' => 'Поле "Имя пользователя" должно быть строкой.',
            'username.min' => 'Поле "Имя пользователя" должно содержать не менее 3 символов.',
            'username.max' => 'Поле "Имя пользователя" не должно превышать 30 символов.',
            'username.regex' => 'Поле "Имя пользователя" должно начинаться с @ и содержать только буквы, цифры, точки и подчеркивания.',
            'password.string' => 'Поле "Новый пароль" должно быть строкой.',
            'password.min' => 'Поле "Новый пароль" должно содержать не менее 8 символов.',
            'password.max' => 'Поле "Новый пароль" не должно превышать 255 символов.',
            'password_confirm.required_with' => 'Поле "Подтверждение пароля" обязательно при заполнении поля "Новый пароль".',
            'password_confirm.same' => 'Поле "Подтверждение пароля" должно совпадать с полем "Новый пароль".',
            'old_password.string' => 'Поле "Старый пароль" должно быть строкой.',
            'password.required_with' => 'Поле "Новый пароль" обязательно при заполнении поля "Старый пароль".',
            'oldPassword.string' => 'Поле "Старый пароль" должно быть строкой.',
        ];
    }
}
