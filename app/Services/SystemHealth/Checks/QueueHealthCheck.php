<?php

namespace App\Services\SystemHealth\Checks;

use App\Services\SystemHealth\Contracts\SystemHealthCheck;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class QueueHealthCheck extends AbstractHealthCheck implements SystemHealthCheck
{
    public function key(): string
    {
        return 'queue';
    }

    public function run(): \App\Services\SystemHealth\HealthCheckResult
    {
        return $this->probe(function (): array {
            if (config('queue.default') === 'sync') {
                return ['message_key' => 'admin_system_health.messages.queue_sync'];
            }

            if (! Schema::hasTable('jobs') || ! Schema::hasTable('failed_jobs')) {
                throw new RuntimeException('Queue tables are missing.');
            }

            $queued = DB::table('jobs')->count();
            $oldest = DB::table('jobs')->min('available_at');
            $oldestSeconds = $oldest ? max(0, now()->timestamp - (int) $oldest) : 0;
            $failed = DB::table('failed_jobs')->where('failed_at', '>=', now()->subDay())->count();
            $context = compact('queued', 'oldestSeconds', 'failed');

            if ($queued >= config('system_health.queue.critical_jobs', 500)
                || $oldestSeconds >= config('system_health.queue.critical_age_seconds', 1800)) {
                return ['status' => 'failing', 'message_key' => 'admin_system_health.messages.queue_critical', 'context' => $context];
            }

            if ($failed > 0
                || $queued >= config('system_health.queue.warning_jobs', 50)
                || $oldestSeconds >= config('system_health.queue.warning_age_seconds', 300)) {
                return ['status' => 'warning', 'message_key' => 'admin_system_health.messages.queue_warning', 'context' => $context];
            }

            return ['message_key' => 'admin_system_health.messages.queue_ok', 'context' => $context];
        });
    }
}
