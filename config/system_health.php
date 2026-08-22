<?php

use App\Services\SystemHealth\Checks\BookingIntegrityHealthCheck;
use App\Services\SystemHealth\Checks\CacheHealthCheck;
use App\Services\SystemHealth\Checks\DatabaseHealthCheck;
use App\Services\SystemHealth\Checks\QueueHealthCheck;
use App\Services\SystemHealth\Checks\StorageHealthCheck;

return [
    'checks' => [
        DatabaseHealthCheck::class,
        CacheHealthCheck::class,
        StorageHealthCheck::class,
        QueueHealthCheck::class,
        BookingIntegrityHealthCheck::class,
    ],
    'storage_disk' => env('SYSTEM_HEALTH_STORAGE_DISK', 'local'),
    'stale_after_minutes' => 12,
    'dashboard_days' => 30,
    'retention_days' => 90,
    'queue' => [
        'warning_jobs' => 50,
        'critical_jobs' => 500,
        'warning_age_seconds' => 300,
        'critical_age_seconds' => 1800,
    ],
];
