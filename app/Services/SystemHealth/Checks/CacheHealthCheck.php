<?php

namespace App\Services\SystemHealth\Checks;

use App\Services\SystemHealth\Contracts\SystemHealthCheck;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

class CacheHealthCheck extends AbstractHealthCheck implements SystemHealthCheck
{
    public function key(): string
    {
        return 'cache';
    }

    public function run(): \App\Services\SystemHealth\HealthCheckResult
    {
        return $this->probe(function (): array {
            $key = 'system-health:'.Str::uuid();
            $value = Str::random(24);

            try {
                Cache::put($key, $value, 30);
                if (Cache::get($key) !== $value) {
                    throw new RuntimeException('Cache read verification failed.');
                }
            } finally {
                Cache::forget($key);
            }

            return ['message_key' => 'admin_system_health.messages.cache_ok'];
        });
    }
}
