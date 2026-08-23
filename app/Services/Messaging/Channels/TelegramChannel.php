<?php

namespace App\Services\Messaging\Channels;

use App\Models\Appointments;
use App\Models\MessagingIntegration;
use App\Services\Messaging\ChannelSendResult;
use App\Services\Messaging\Contracts\MessagingChannel;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class TelegramChannel implements MessagingChannel
{
    public function provider(): string { return 'telegram'; }

    public function recipient(Appointments $appointment): ?string
    {
        return filled($appointment->customer?->messaging_contacts['telegram_chat_id'] ?? null)
            ? (string) $appointment->customer->messaging_contacts['telegram_chat_id']
            : null;
    }

    public function send(MessagingIntegration $integration, Appointments $appointment, string $trigger, string $message, string $locale): ChannelSendResult
    {
        $token = (string) (($integration->credentials ?? [])['bot_token'] ?? '');
        try {
            $response = Http::acceptJson()->timeout(10)
                ->post('https://api.telegram.org/bot'.$token.'/sendMessage', [
                    'chat_id' => $this->recipient($appointment),
                    'text' => $message,
                    'disable_web_page_preview' => false,
                ]);
        } catch (ConnectionException $exception) {
            return ChannelSendResult::failed($exception->getMessage(), false);
        }

        $body = $response->json() ?: [];
        if ($response->successful() && ($body['ok'] ?? false)) {
            return ChannelSendResult::accepted((string) data_get($body, 'result.message_id'), $body);
        }

        return ChannelSendResult::failed((string) ($body['description'] ?? 'Telegram rejected the message'), $response->clientError(), $body);
    }
}
