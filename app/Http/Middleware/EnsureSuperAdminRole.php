<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role?->resolvedSlug() === 'super-admin') {
            return $next($request);
        }

        abort(403, __('admin_roles.access_denied'));
    }
}
