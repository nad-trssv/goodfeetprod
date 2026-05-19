<?php

return [
    'enabled' => env('MAINTENANCE_ENABLED', false),

    'allowed_ips' => array_filter(array_map('trim', explode(',', env('MAINTENANCE_ALLOWED_IPS', '')))),

    'allow_admin' => env('MAINTENANCE_ALLOW_ADMIN', true),
];
