<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use App\Models\User;

class CrmCustomerProfileRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasPermission('crm.update') === true; }
    public function rules(): array
    {
        return [
            'contraindications'=>['nullable','string','max:10000'],
            'important_warnings'=>['nullable','string','max:10000'],
            'preferred_user_id'=>['nullable','integer',Rule::exists('users','id')],
            'preferred_service_ids'=>['nullable','array','max:50'],
            'preferred_service_ids.*'=>['integer',Rule::exists('services','id')->where(fn($q)=>$q->where('status',true)->where('is_deleted',false))],
            'tag_ids'=>['nullable','array','max:50'],
            'tag_ids.*'=>['integer',Rule::exists('crm_tags','id')],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->filled('preferred_user_id')) {
                $user = User::with('role.permissions')->find($this->integer('preferred_user_id'));
                if (! $user?->isServiceProvider()) {
                    $validator->errors()->add('preferred_user_id', __('crm.invalid_preferred_master'));
                }
            }
        }];
    }
}
