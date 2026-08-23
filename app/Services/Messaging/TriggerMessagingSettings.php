<?php

namespace App\Services\Messaging;

use App\Models\MessagingIntegration;
use App\Models\MessagingTriggerSetting;
use Illuminate\Support\Facades\DB;

class TriggerMessagingSettings
{
    public function __construct(private readonly TriggerScheduleService $scheduler) {}

    public function update(array $validated): void
    {
        DB::transaction(function () use ($validated) {
            foreach (MessagingTriggerSetting::TRIGGERS as $trigger) {
                $data = $validated['triggers'][$trigger];
                MessagingTriggerSetting::query()->where('trigger', $trigger)->update([
                    'enabled' => (bool) $data['enabled'],
                    'timing_minutes' => (int) $data['timing_minutes'],
                    'templates' => json_encode(collect($data['templates'])
                        ->map(fn ($message) => trim(strip_tags((string) $message)))->all(), JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
            }
            foreach ($validated['priorities'] as $provider => $priority) {
                MessagingIntegration::firstOrCreate(['provider' => $provider], [
                    'enabled' => false, 'settings' => [], 'credentials' => null,
                ])->update(['priority' => (int) $priority]);
            }
        });

        $this->scheduler->syncCurrentAppointments();
    }
}
