<?php

namespace App\Services\SystemHealth;

use App\Models\SystemHealthRun;
use App\Services\SystemHealth\Contracts\SystemHealthCheck;
use Throwable;

class SystemHealthMonitor
{
    public function run(string $source = 'scheduled'): SystemHealthRun
    {
        $startedAt = now();
        $startedClock = hrtime(true);
        $run = SystemHealthRun::create([
            'status' => 'running',
            'source' => in_array($source, ['scheduled', 'manual', 'cli'], true) ? $source : 'cli',
            'started_at' => $startedAt,
        ]);

        $worstStatus = 'operational';

        foreach (config('system_health.checks', []) as $checkClass) {
            $result = $this->execute($checkClass);
            $run->checks()->create([
                'key' => $result->key,
                'status' => $result->status,
                'duration_ms' => $result->durationMs,
                'message_key' => $result->messageKey,
                'context' => $result->context,
            ]);

            if ($result->status === 'failing') {
                $worstStatus = 'outage';
            } elseif ($result->status === 'warning' && $worstStatus === 'operational') {
                $worstStatus = 'degraded';
            }
        }

        if ($run->checks()->count() === 0) {
            $worstStatus = 'outage';
        }

        $run->update([
            'status' => $worstStatus,
            'duration_ms' => max(0, (int) round((hrtime(true) - $startedClock) / 1_000_000)),
            'finished_at' => now(),
        ]);

        SystemHealthRun::query()
            ->where('started_at', '<', now()->subDays((int) config('system_health.retention_days', 90)))
            ->delete();

        return $run->fresh('checks');
    }

    private function execute(string $checkClass): HealthCheckResult
    {
        $startedAt = hrtime(true);

        try {
            $check = app($checkClass);
            if (! $check instanceof SystemHealthCheck) {
                throw new \LogicException($checkClass.' must implement SystemHealthCheck.');
            }

            return $check->run();
        } catch (Throwable $exception) {
            report($exception);

            return new HealthCheckResult(
                class_basename($checkClass),
                'failing',
                max(0, (int) round((hrtime(true) - $startedAt) / 1_000_000)),
                'admin_system_health.messages.check_failed',
                ['exception' => class_basename($exception)],
            );
        }
    }
}
