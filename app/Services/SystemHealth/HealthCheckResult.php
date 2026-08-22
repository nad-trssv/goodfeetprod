<?php

namespace App\Services\SystemHealth;

final readonly class HealthCheckResult
{
    public function __construct(
        public string $key,
        public string $status,
        public int $durationMs,
        public string $messageKey,
        public array $context = [],
    ) {
    }
}
