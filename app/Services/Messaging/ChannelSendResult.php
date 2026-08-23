<?php

namespace App\Services\Messaging;

final readonly class ChannelSendResult
{
    public function __construct(
        public bool $accepted,
        public bool $definitiveFailure,
        public ?string $externalId = null,
        public ?string $error = null,
        public array $response = [],
    ) {}

    public static function accepted(?string $externalId, array $response = []): self
    {
        return new self(true, false, $externalId, null, $response);
    }

    public static function failed(string $error, bool $definitive = true, array $response = []): self
    {
        return new self(false, $definitive, null, $error, $response);
    }
}
