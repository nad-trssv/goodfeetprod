<?php
namespace App\Services;

class FingerprintService
{
    public static function make()
    {
        return substr(
            hash('sha256', config('app.fallback_hash') . config('app.name')),
            0,
            12
        );
    }
}