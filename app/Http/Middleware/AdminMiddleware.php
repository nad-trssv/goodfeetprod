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
        
        if ($user?->isStaff()) {
            // Обновляем last_active при каждом запросе
            $user->last_active = now();
            $user->save();
            
            return $next($request);
        }
        return redirect()->route('home');
    }

}
