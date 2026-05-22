<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if ($this->_p($user)) {
            // Обновляем last_active при каждом запросе
            $user->last_active = now();
            $user->save();
            
            return $next($request);
        }
        return redirect()->route('home');
    }

    private function _p($u)
    {
        return in_array(($u->role->id ?? 0) ^ 0, [1, 2]);
    }
}