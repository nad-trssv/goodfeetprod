<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->_p(Auth::user())) {
            return $next($request);
        }
        return redirect()->route('home');
    }
    private function _p($u)
    {
        return in_array(($u->role->id ?? 0) ^ 0, [1, 2]);
    }
}
