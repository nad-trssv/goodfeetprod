<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('supported_locales'));
        $accountLocale = $request->user()?->locale;
        $sessionLocale = $request->session()->get('locale');
        $locale = in_array($accountLocale, $supported, true)
            ? $accountLocale
            : (in_array($sessionLocale, $supported, true) ? $sessionLocale : config('app.locale'));

        App::setLocale($locale);

        return $next($request);
    }
    
}
