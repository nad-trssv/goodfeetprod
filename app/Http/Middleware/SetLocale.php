<?php

namespace App\Http\Middleware;

use App\Models\SiteTranslation;
use App\Services\Localization\SiteLocaleRegistry;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
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
        $staff = $request->user('web');
        $customer = $request->user('customer');
        $routeMiddleware = $request->route()?->gatherMiddleware() ?? [];
        $isBackOffice = ($staff?->isStaff() ?? false) && collect($routeMiddleware)->contains(
            fn (string $middleware) => $middleware === 'admin' || $middleware === AdminMiddleware::class,
        );
        $siteLocales = app(SiteLocaleRegistry::class);
        $supported = $isBackOffice
            ? array_keys(config('supported_locales'))
            : array_keys($siteLocales->activeLabels());
        $default = $isBackOffice ? config('app.locale') : $siteLocales->defaultCode();
        $accountLocale = $isBackOffice ? $staff?->locale : $customer?->locale;
        $sessionLocale = $request->session()->get('locale');
        $locale = $isBackOffice
            ? (in_array($accountLocale, $supported, true)
                ? $accountLocale
                : (in_array($sessionLocale, $supported, true) ? $sessionLocale : $default))
            : (in_array($sessionLocale, $supported, true)
                ? $sessionLocale
                : (in_array($accountLocale, $supported, true) ? $accountLocale : $default));

        App::setLocale($locale);

        if (! $isBackOffice && Schema::hasTable('site_translations')) {
            Lang::addLines(
                SiteTranslation::query()->where('locale', $locale)->pluck('value', 'translation_key')->all(),
                $locale,
            );
        }

        return $next($request);
    }
    
}
