<?php

namespace App\Http\Requests;

use App\Services\Localization\SiteLocaleRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrmSettingsRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasAllAppointmentsScope()===true && $this->user()?->hasPermission('crm.settings')===true; }

    protected function prepareForValidation(): void
    {
        $default = app(SiteLocaleRegistry::class)->defaultCode();
        $localized = [];
        foreach (['title', 'welcome_message', 'offline_message'] as $key) {
            if (is_string($this->input($key))) {
                $localized[$key] = [$default => $this->input($key)];
            }
        }
        if ($localized !== []) {
            $this->merge($localized);
        }
    }

    public function rules(): array
    {
        $locales = array_keys(app(SiteLocaleRegistry::class)->installedLabels());
        $default = app(SiteLocaleRegistry::class)->defaultCode();

        return [
            'enabled'=>['required','boolean'],'notify_client_staff_events'=>['required','boolean'],
            'title'=>['required','array:'.implode(',', $locales)],
            'title.'.$default=>['required','string','max:100'],
            'title.*'=>['nullable','string','max:100'],
            'welcome_message'=>['required','array:'.implode(',', $locales)],
            'welcome_message.'.$default=>['required','string','max:1000'],
            'welcome_message.*'=>['nullable','string','max:1000'],
            'offline_message'=>['required','array:'.implode(',', $locales)],
            'offline_message.'.$default=>['required','string','max:1000'],
            'offline_message.*'=>['nullable','string','max:1000'],
            'timezone'=>['required','timezone:all'],
            'schedule'=>['required','array'],'schedule.*.enabled'=>['required','boolean'],
            'schedule.*.start'=>['required','date_format:H:i'],'schedule.*.end'=>['required','date_format:H:i','after:schedule.*.start'],
            'staff'=>['nullable','array'],'staff.*.user_id'=>['required','integer',Rule::exists('users','id')],
            'staff.*.is_enabled'=>['nullable','boolean'],'staff.*.can_view_history'=>['nullable','boolean'],'staff.*.must_answer'=>['nullable','boolean'],
        ];
    }
}
