<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrmSettingsRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasAllAppointmentsScope()===true && $this->user()?->hasPermission('crm.settings')===true; }
    public function rules(): array
    {
        return [
            'enabled'=>['required','boolean'],'title'=>['required','string','max:100'],
            'welcome_message'=>['required','string','max:1000'],'offline_message'=>['required','string','max:1000'],
            'timezone'=>['required','timezone:all'],
            'schedule'=>['required','array'],'schedule.*.enabled'=>['required','boolean'],
            'schedule.*.start'=>['required','date_format:H:i'],'schedule.*.end'=>['required','date_format:H:i','after:schedule.*.start'],
            'staff'=>['nullable','array'],'staff.*.user_id'=>['required','integer',Rule::exists('users','id')],
            'staff.*.is_enabled'=>['nullable','boolean'],'staff.*.can_view_history'=>['nullable','boolean'],'staff.*.must_answer'=>['nullable','boolean'],
        ];
    }
}
