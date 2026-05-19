<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('maintenance.enabled')) {
            return $next($request);
        }

        $ip = $request->ip();
        $allowedIps = config('maintenance.allowed_ips') ?? [];

        if (in_array($ip, $allowedIps, true)) {
            return $next($request);
        }

        if (config('maintenance.allow_admin') && $request->is('admin/*')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Service temporarily unavailable',
            ], 503);
        }

        return response()->view('maintenance', [], 503);
    }
}
