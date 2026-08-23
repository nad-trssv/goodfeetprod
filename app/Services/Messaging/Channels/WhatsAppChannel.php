<?php

namespace App\Services\Messaging\Channels;

use App\Models\Appointments;
use App\Models\MessagingIntegration;
use App\Services\Messaging\ChannelSendResult;
use App\Services\Messaging\Contracts\MessagingChannel;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class WhatsAppChannel implements MessagingChannel
{
    public function provider(): string { return 'whatsapp'; }

    public function recipient(Appointments $appointment): ?string
    {
        $number = preg_replace('/\D+/', '', (string) $appointment->client_phone);

        return strlen($number) >= 8 ? $number : null;
    }

    public function send(MessagingIntegration $integration, Appointments $appointment, string $trigger, string $message, string $locale): ChannelSendResult
    {
        $settings = $integration->settings ?? [];
        $credentials = $integration->credentials ?? [];
        $template = trim((string) ($settings[$trigger.'_template'] ?? ''));
        if ($template === '') {
            return ChannelSendResult::failed('An approved WhatsApp template is not configured for '.$trigger);
        }

        $languageMap = json_decode((string) ($settings['template_language_codes'] ?? ''), true);
        $language = is_array($languageMap) ? ($languageMap[$locale] ?? $locale) : $locale;
        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            rawurlencode((string) config('messaging_integrations.whatsapp_graph_version')),
            rawurlencode((string) ($settings['phone_number_id'] ?? '')),
        );

        try {
            $response = Http::acceptJson()->withToken((string) ($credentials['access_token'] ?? ''))
                ->timeout(10)->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $this->recipient($appointment),
                    'type' => 'template',
                    'template' => [
                        'name' => $template,
                        'language' => ['code' => $language],
                        'components' => [[
                            'type' => 'body',
                            'parameters' => [['type' => 'text', 'text' => $message]],
                        ]],
                    ],
                ]);
        } catch (ConnectionException $exception) {
            return ChannelSendResult::failed($exception->getMessage(), false);
        }

        $body = $response->json() ?: [];
        if ($response->successful() && filled(data_get($body, 'messages.0.id'))) {
            return ChannelSendResult::accepted((string) data_get($body, 'messages.0.id'), $body);
        }

        return ChannelSendResult::failed(
            (string) data_get($body, 'error.message', 'WhatsApp rejected the message'),
            $response->clientError(),
            $body,
        );
    }
}
