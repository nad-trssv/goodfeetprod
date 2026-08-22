<?php

namespace App\Console\Commands;

use App\Services\SystemHealth\SystemHealthMonitor;
use Illuminate\Console\Command;

class RunSystemHealthChecks extends Command
{
    protected $signature = 'system:health-check {--source=cli}';

    protected $description = 'Run internal system health checks and save their results';

    public function handle(SystemHealthMonitor $monitor): int
    {
        $run = $monitor->run((string) $this->option('source'));
        $this->components->info("System health: {$run->status} ({$run->duration_ms} ms)");

        return $run->status === 'outage' ? self::FAILURE : self::SUCCESS;
    }
}
