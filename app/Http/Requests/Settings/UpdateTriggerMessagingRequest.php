<?php

namespace App\Http\Requests\Settings;

use App\Models\MessagingTriggerSetting;
use App\Services\Localization\SiteLocaleRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTriggerMessagingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->resolvedSlug() === 'super-admin';
    }

    public function rules(): array
    {
        $rules = [
            'triggers' => ['required', 'array'],
            'priorities' => ['required', 'array', 'size:'.count(config('messaging_integrations.providers'))],
        ];
        foreach (MessagingTriggerSetting::TRIGGERS as $trigger) {
            $rules["triggers.$trigger.enabled"] = ['required', 'boolean'];
            $rules["triggers.$trigger.timing_minutes"] = $trigger === 'appointment_reminder'
                ? ['required', 'integer', 'between:60,10080']
                : ($trigger === 'review_request' ? ['required', 'integer', 'between:0,720'] : ['required', 'integer', 'in:0']);
            foreach (array_keys(app(SiteLocaleRegistry::class)->installedLabels()) as $locale) {
                $rules["triggers.$trigger.templates.$locale"] = ['required', 'string', 'max:2000'];
            }
        }
        foreach (array_keys(config('messaging_integrations.providers')) as $provider) {
            $rules["priorities.$provider"] = ['required', 'integer', 'between:1,'.count(config('messaging_integrations.providers'))];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $priorities = array_map('intval', $this->input('priorities', []));
            if (count($priorities) !== count(array_unique($priorities))) {
                $validator->errors()->add('priorities', __('admin_messaging.priority_unique'));
            }
            foreach ($this->input('triggers', []) as $trigger => $data) {
                foreach (($data['templates'] ?? []) as $locale => $template) {
                    $required = $trigger === 'review_request'
                        ? ['{feedback_url}']
                        : ['{service}', '{date}', '{time}'];
                    foreach ($required as $placeholder) {
                        if (! str_contains((string) $template, $placeholder)) {
                            $validator->errors()->add("triggers.$trigger.templates.$locale", __('admin_messaging.placeholder_required', ['placeholder' => $placeholder]));
                        }
                    }
                }
            }
        });
    }
}
