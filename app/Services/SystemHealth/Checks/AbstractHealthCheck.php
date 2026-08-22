<?php

namespace App\Services\SystemHealth\Checks;

use App\Services\SystemHealth\HealthCheckResult;
use Closure;
use Throwable;

abstract class AbstractHealthCheck
{
    protected function probe(Closure $callback): HealthCheckResult
    {
        $startedAt = hrtime(true);

        try {
            $payload = $callback();

            return new HealthCheckResult(
                $this->key(),
                $payload['status'] ?? 'ok',
                $this->elapsedMilliseconds($startedAt),
                $payload['message_key'] ?? 'admin_system_health.messages.ok',
                $payload['context'] ?? [],
            );
        } catch (Throwable $exception) {
            report($exception);

            return new HealthCheckResult(
                $this->key(),
                'failing',
                $this->elapsedMilliseconds($startedAt),
                'admin_system_health.messages.check_failed',
                ['exception' => class_basename($exception)],
            );
        }
    }

    private function elapsedMilliseconds(int $startedAt): int
    {
        return max(0, (int) round((hrtime(true) - $startedAt) / 1_000_000));
    }
}
