<?php

namespace App\Services\Messaging;

use App\Models\MessagingIntegration;
use App\Models\MessagingTriggerSetting;
use App\Models\TriggerMessageAttempt;
use App\Models\TriggerMessageDispatch;
use App\Services\Messaging\Channels\TelegramChannel;
use App\Services\Messaging\Channels\ViberChannel;
use App\Services\Messaging\Channels\WhatsAppChannel;
use App\Services\Messaging\Contracts\MessagingChannel;
use Illuminate\Support\Facades\DB;
use Throwable;

class TriggerMessageDispatcher
{
    /** @var array<string, MessagingChannel> */
    private array $channels;

    public function __construct(
        WhatsAppChannel $whatsapp,
        ViberChannel $viber,
        TelegramChannel $telegram,
        private readonly TriggerTemplateRenderer $renderer,
    ) {
        $this->channels = collect([$whatsapp, $viber, $telegram])->keyBy->provider()->all();
    }

    public function processDue(int $limit = 50): int
    {
        $enabledTriggers = MessagingTriggerSetting::query()->where('enabled', true)->pluck('trigger');
        if ($enabledTriggers->isEmpty()) {
            return 0;
        }

        $processed = 0;
        while ($processed < $limit && ($dispatch = $this->claim($enabledTriggers->all()))) {
            $this->process($dispatch);
            $processed++;
        }

        return $processed;
    }

    private function claim(array $enabledTriggers): ?TriggerMessageDispatch
    {
        return DB::transaction(function () use ($enabledTriggers) {
            $dispatch = TriggerMessageDispatch::query()
                ->where('status', 'pending')
                ->whereIn('trigger', $enabledTriggers)
                ->where('scheduled_at', '<=', now())
                ->orderBy('scheduled_at')->lockForUpdate()->first();
            if ($dispatch) {
                $dispatch->update(['status' => 'processing']);
            }

            return $dispatch;
        });
    }

    private function process(TriggerMessageDispatch $dispatch): void
    {
        $dispatch->load('appointment.customer');
        $appointment = $dispatch->appointment;
        if (! $appointment || ($dispatch->expires_at && $dispatch->expires_at->isPast())) {
            $dispatch->update(['status' => 'skipped', 'last_error' => 'Message window expired']);
            return;
        }
        if (! $this->eligible($dispatch)) {
            $dispatch->update(['status' => 'pending', 'scheduled_at' => now()->addMinutes(5)]);
            return;
        }

        $setting = MessagingTriggerSetting::where('trigger', $dispatch->trigger)->firstOrFail();
        $message = $this->renderer->render($setting, $appointment);
        $locale = $this->renderer->locale($appointment);
        $integrations = MessagingIntegration::query()->where('enabled', true)
            ->orderBy('priority')->orderBy('provider')->get();
        $failedProviders = $dispatch->attempts()->where('status', 'failed')->pluck('provider')->all();
        $lastError = 'No enabled and configured channel has a recipient';

        foreach ($integrations as $integration) {
            if (in_array($integration->provider, $failedProviders, true)) {
                continue;
            }
            $channel = $this->channels[$integration->provider] ?? null;
            if (! $channel || ! $integration->isConfigured()) {
                continue;
            }
            $recipient = $channel->recipient($appointment);
            if (! $recipient) {
                TriggerMessageAttempt::create([
                    'dispatch_id' => $dispatch->id, 'provider' => $integration->provider,
                    'status' => 'skipped', 'error' => 'Customer is not linked to this channel', 'attempted_at' => now(),
                ]);
                continue;
            }

            try {
                $result = $channel->send($integration, $appointment, $dispatch->trigger, $message, $locale);
            } catch (Throwable $exception) {
                $result = ChannelSendResult::failed($exception->getMessage(), false);
            }
            TriggerMessageAttempt::create([
                'dispatch_id' => $dispatch->id, 'provider' => $integration->provider,
                'status' => $result->accepted ? 'sent' : ($result->definitiveFailure ? 'failed' : 'unknown'),
                'recipient' => $recipient, 'external_id' => $result->externalId,
                'error' => $result->error, 'response' => $result->response ?: null, 'attempted_at' => now(),
            ]);

            if ($result->accepted) {
                $dispatch->update([
                    'status' => 'sent', 'sent_provider' => $integration->provider,
                    'sent_at' => now(), 'last_error' => null,
                ]);
                return;
            }
            $lastError = $result->error ?: $lastError;
            if (! $result->definitiveFailure) {
                $dispatch->update(['status' => 'unknown', 'last_error' => $lastError]);
                return;
            }
        }

        $dispatch->update([
            'status' => 'pending', 'scheduled_at' => now()->addMinutes(15), 'last_error' => $lastError,
        ]);
    }

    private function eligible(TriggerMessageDispatch $dispatch): bool
    {
        $appointment = $dispatch->appointment;

        return match ($dispatch->trigger) {
            'booking_created', 'appointment_reminder' => in_array($appointment->status, $appointment::BLOCKING_STATUSES, true),
            'review_request' => $appointment->status === 'completed' && ! $appointment->feedback?->submitted_at,
            default => false,
        };
    }
}
