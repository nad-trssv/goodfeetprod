<?php

namespace App\Services\Messaging;

use App\Models\MessagingIntegration;

class MessagingIntegrationSettings
{
    public function update(string $provider, array $validated): MessagingIntegration
    {
        $definition = config('messaging_integrations.providers.'.$provider);
        abort_unless(is_array($definition), 404);

        $integration = MessagingIntegration::firstOrNew(['provider' => $provider]);
        $allowedSettings = array_keys($definition['settings']);
        $settings = collect($validated['settings'] ?? [])
            ->only($allowedSettings)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->all();

        $credentials = $integration->credentials ?? [];
        if ((bool) ($validated['clear_credentials'] ?? false)) {
            $credentials = [];
        } else {
            foreach ($definition['credentials'] as $key) {
                $value = $validated['credentials'][$key] ?? null;
                if (filled($value)) {
                    $credentials[$key] = trim((string) $value);
                }
            }
        }

        $integration->fill([
            'enabled' => (bool) $validated['enabled'],
            'settings' => $settings,
            'credentials' => $credentials ?: null,
        ])->save();

        return $integration->fresh();
    }
}
