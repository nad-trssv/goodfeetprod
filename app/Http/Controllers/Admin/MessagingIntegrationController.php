<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateMessagingIntegrationRequest;
use App\Services\Messaging\MessagingIntegrationSettings;
use Illuminate\Http\RedirectResponse;

class MessagingIntegrationController extends Controller
{
    public function update(
        UpdateMessagingIntegrationRequest $request,
        string $provider,
        MessagingIntegrationSettings $settings,
    ): RedirectResponse {
        abort_unless(array_key_exists($provider, config('messaging_integrations.providers')), 404);
        $integration = $settings->update($provider, $request->validated());

        return redirect()
            ->to(route('settings.index').'#tab-integrations')
            ->with('settings_tab', 'integrations')
            ->with('success', __('admin_messaging.saved', [
                'provider' => __('admin_messaging.providers.'.$provider.'.name'),
            ]))
            ->with('integration_provider', $integration->provider);
    }
}
