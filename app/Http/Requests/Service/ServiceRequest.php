<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        return $user?->hasPermission($this->isMethod('post') ? 'services.create' : 'services.update') === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'masters' => 'required|array',
            'masters.*' => ['integer', 'exists:users,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            foreach ((array) $this->input('masters', []) as $masterId) {
                $master = \App\Models\User::find($masterId);
                if (! $master || ! $master->isServiceProvider()) {
                    $validator->errors()->add('masters', __('admin_validation.masters.exists'));
                    break;
                }
            }
        });
    }

    public function messages()
    {
        return [
            'masters.required' => __('admin_validation.masters.required'),
            'masters.array' => __('admin_validation.masters.array'),
            'masters.*.exists' => __('admin_validation.masters.exists'),
        ];
    }
    
    public function getMasters()
    {
        return $this->input('masters');
    }
}
