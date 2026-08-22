<?php

namespace App\Services\SystemHealth\Checks;

use App\Services\SystemHealth\Contracts\SystemHealthCheck;
use Illuminate\Support\Facades\DB;

class DatabaseHealthCheck extends AbstractHealthCheck implements SystemHealthCheck
{
    public function key(): string
    {
        return 'database';
    }

    public function run(): \App\Services\SystemHealth\HealthCheckResult
    {
        return $this->probe(function (): array {
            DB::select('SELECT 1');

            return ['message_key' => 'admin_system_health.messages.database_ok'];
        });
    }
}
