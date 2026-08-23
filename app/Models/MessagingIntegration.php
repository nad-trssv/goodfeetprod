<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessagingIntegration extends Model
{
    protected $fillable = ['provider', 'enabled', 'priority', 'settings', 'credentials'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'priority' => 'integer',
            'settings' => 'array',
            'credentials' => 'encrypted:array',
        ];
    }

    public function hasCredential(string $key): bool
    {
        return filled(($this->credentials ?? [])[$key] ?? null);
    }

    public function isConfigured(): bool
    {
        $definition = config('messaging_integrations.providers.'.$this->provider, []);
        $settings = $this->settings ?? [];
        $credentials = $this->credentials ?? [];

        foreach ($definition['required_settings'] ?? [] as $key) {
            if (! filled($settings[$key] ?? null)) {
                return false;
            }
        }

        foreach ($definition['required_credentials'] ?? [] as $key) {
            if (! filled($credentials[$key] ?? null)) {
                return false;
            }
        }

        return true;
    }
}
