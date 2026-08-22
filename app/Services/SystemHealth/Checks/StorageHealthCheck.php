<?php

namespace App\Services\SystemHealth\Checks;

use App\Services\SystemHealth\Contracts\SystemHealthCheck;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class StorageHealthCheck extends AbstractHealthCheck implements SystemHealthCheck
{
    public function key(): string
    {
        return 'storage';
    }

    public function run(): \App\Services\SystemHealth\HealthCheckResult
    {
        return $this->probe(function (): array {
            $disk = Storage::disk(config('system_health.storage_disk', 'local'));
            $path = 'system-health/'.Str::uuid().'.probe';
            $value = Str::random(24);

            try {
                if (! $disk->put($path, $value) || $disk->get($path) !== $value) {
                    throw new RuntimeException('Storage verification failed.');
                }
            } finally {
                $disk->delete($path);
            }

            return ['message_key' => 'admin_system_health.messages.storage_ok'];
        });
    }
}
