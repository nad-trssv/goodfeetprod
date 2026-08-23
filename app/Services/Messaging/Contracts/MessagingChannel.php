<?php

namespace App\Services\Messaging\Contracts;

use App\Models\Appointments;
use App\Models\MessagingIntegration;
use App\Services\Messaging\ChannelSendResult;

interface MessagingChannel
{
    public function provider(): string;

    public function recipient(Appointments $appointment): ?string;

    public function send(
        MessagingIntegration $integration,
        Appointments $appointment,
        string $trigger,
        string $message,
        string $locale,
    ): ChannelSendResult;
}
