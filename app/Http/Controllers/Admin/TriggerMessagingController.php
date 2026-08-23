<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateTriggerMessagingRequest;
use App\Services\Messaging\TriggerMessagingSettings;
use Illuminate\Http\RedirectResponse;

class TriggerMessagingController extends Controller
{
    public function update(UpdateTriggerMessagingRequest $request, TriggerMessagingSettings $settings): RedirectResponse
    {
        $settings->update($request->validated());

        return redirect()->to(route('settings.index').'#tab-integrations')
            ->with('settings_tab', 'integrations')
            ->with('success', __('admin_messaging.automation_saved'));
    }
}
