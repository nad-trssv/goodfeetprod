<?php

namespace App\Services\SystemHealth\Contracts;

use App\Services\SystemHealth\HealthCheckResult;

interface SystemHealthCheck
{
    public function key(): string;

    public function run(): HealthCheckResult;
}
