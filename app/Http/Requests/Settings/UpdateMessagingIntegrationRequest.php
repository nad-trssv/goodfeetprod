<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMessagingIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->resolvedSlug() === 'super-admin';
    }

    public function rules(): array
    {
        $definition = config('messaging_integrations.providers.'.$this->route('provider'), []);
        $rules = [
            'enabled' => ['required', 'boolean'],
            'clear_credentials' => ['sometimes', 'boolean'],
            'settings' => ['sometimes', 'array'],
            'credentials' => ['sometimes', 'array'],
        ];

        foreach ($definition['settings'] ?? [] as $key => $type) {
            $rules['settings.'.$key] = match ($type) {
                'numeric_id' => ['nullable', 'string', 'max:64', 'regex:/^\d+$/'],
                'bot_username' => ['nullable', 'string', 'max:33', 'regex:/^@?[A-Za-z0-9_]{5,32}$/'],
                'url' => ['nullable', 'url', 'max:2048'],
                default => ['nullable', 'string', 'max:255'],
            };
        }

        foreach ($definition['credentials'] ?? [] as $key) {
            $rules['credentials.'.$key] = $key === 'webhook_secret'
                ? ['nullable', 'string', 'min:1', 'max:256', 'regex:/^[A-Za-z0-9_-]+$/']
                : ['nullable', 'string', 'max:4096'];
        }

        return $rules;
    }
}
