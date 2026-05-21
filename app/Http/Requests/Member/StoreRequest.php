<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:users,phone',
            'role_id' => 'required|exists:roles,id',
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^@(?!.*\.\.)(?!.*\.$)[a-zA-Z0-9._]+$/',
                'unique:users,username',
            ],
        ];

        $step = $this->route('step');

        if ($step == 1) {
            $rules['profile_photo_path'] = 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:min_width=70,min_height=70';
        }

        if ($step == 2) {
            $rules['profile_photo_path'] = 'nullable|string';
            $rules['email'] = 'required|email|unique:users,email';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        if ($step == 3) {
            $rules['services'] = 'nullable|array';
            $rules['services.*'] = 'exists:services,id';
        }

        if ($step == 4) {
            $rules['monday_start'] = 'nullable|date_format:H:i';
            $rules['monday_end'] = 'nullable|date_format:H:i';
            $rules['tuesday_start'] = 'nullable|date_format:H:i';
            $rules['tuesday_end'] = 'nullable|date_format:H:i';
            $rules['wednesday_start'] = 'nullable|date_format:H:i';
            $rules['wednesday_end'] = 'nullable|date_format:H:i';
            $rules['thursday_start'] = 'nullable|date_format:H:i';
            $rules['thursday_end'] = 'nullable|date_format:H:i';
            $rules['friday_start'] = 'nullable|date_format:H:i';
            $rules['friday_end'] = 'nullable|date_format:H:i';
            $rules['saturday_start'] = 'nullable|date_format:H:i';
            $rules['saturday_end'] = 'nullable|date_format:H:i';
            $rules['sunday_start'] = 'nullable|date_format:H:i';
            $rules['sunday_end'] = 'nullable|date_format:H:i';
            $rules['lunch_start'] = 'nullable|date_format:H:i';
            $rules['lunch_end'] = 'nullable|date_format:H:i';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'phone.required' => 'The phone number is required.',
            'phone.string' => 'The phone number must be a string.',
            'phone.max' => 'The phone number may not be greater than 15 characters.',
            'phone.unique' => 'The phone number has already been taken.',
            'role_id.required' => 'The role field is required.',
            'role_id.exists' => 'The selected role is invalid.',
            'username.required' => 'The username is required.',
            'username.string' => 'The username must be a string.',
            'username.min' => 'The username must be at least 3 characters.',
            'username.max' => 'The username may not be greater than 30 characters.',
            'username.regex' => 'The username format is invalid.',
            'username.unique' => 'The username has already been taken.',
            'profile_photo_path.image' => 'The profile photo must be an image.',
            'profile_photo_path.mimes' => 'The profile photo must be a file of type: jpg, jpeg, png, webp.',
            'profile_photo_path.max' => 'The profile photo may not be greater than 5MB.',
            'profile_photo_path.dimensions' => 'The profile photo must have at least 70x70 pixels.',
            'email.required' => 'The email address is required.',
            'email.email' => 'The email address must be a valid email address.',
            'email.unique' => 'The email address has already been taken.',
            'password.required' => 'The password is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'services.array' => 'Services must be an array.',
            'services.*.exists' => 'One or more selected services are invalid.',
        ];
    }
}