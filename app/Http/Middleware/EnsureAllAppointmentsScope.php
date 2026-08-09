<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAllAppointmentsScope
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->hasAllAppointmentsScope()) {
            return $next($request);
        }

        $message = __('admin_roles.access_denied');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        if (! $request->isMethod('get')) {
            return back()->with('authorization_error', $message);
        }

        abort(403, $message);
    }
}
