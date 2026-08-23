<?php

namespace App\Services\Messaging\Channels;

use App\Models\Appointments;
use App\Models\MessagingIntegration;
use App\Services\Messaging\ChannelSendResult;
use App\Services\Messaging\Contracts\MessagingChannel;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ViberChannel implements MessagingChannel
{
    public function provider(): string { return 'viber'; }

    public function recipient(Appointments $appointment): ?string
    {
        return filled($appointment->customer?->messaging_contacts['viber_user_id'] ?? null)
            ? (string) $appointment->customer->messaging_contacts['viber_user_id']
            : null;
    }

    public function send(MessagingIntegration $integration, Appointments $appointment, string $trigger, string $message, string $locale): ChannelSendResult
    {
        $settings = $integration->settings ?? [];
        $credentials = $integration->credentials ?? [];
        $sender = ['name' => (string) ($settings['bot_name'] ?? config('app.name'))];
        if (filled($settings['sender_avatar_url'] ?? null)) {
            $sender['avatar'] = $settings['sender_avatar_url'];
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders(['X-Viber-Auth-Token' => (string) ($credentials['auth_token'] ?? '')])
                ->timeout(10)->post('https://chatapi.viber.com/pa/send_message', [
                    'receiver' => $this->recipient($appointment),
                    'min_api_version' => 7,
                    'sender' => $sender,
                    'type' => 'text',
                    'text' => $message,
                ]);
        } catch (ConnectionException $exception) {
            return ChannelSendResult::failed($exception->getMessage(), false);
        }

        $body = $response->json() ?: [];
        if ($response->successful() && (int) ($body['status'] ?? -1) === 0) {
            return ChannelSendResult::accepted((string) ($body['message_token'] ?? ''), $body);
        }

        $providerRejected = $response->successful() && array_key_exists('status', $body) && (int) $body['status'] !== 0;

        return ChannelSendResult::failed(
            (string) ($body['status_message'] ?? 'Viber rejected the message'),
            $response->clientError() || $providerRejected,
            $body,
        );
    }
}
