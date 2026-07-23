<?php

namespace App\Http\Controllers;

use App\Models\Services;
use App\Models\SiteSettings;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class PageController extends Controller
{
    public function sitemap(): Response
    {
        return response()
            ->view('seo.sitemap', ['routes' => ['home', 'clientservice', 'gallery.index', 'contacts', 'policy']])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function index(): View
    {
        $locale = app()->getLocale();
        $today = Carbon::now()->toDateString();

        $services = Services::with(['rules', 'futureRules', 'translations'])
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($service) use ($locale, $today) {
                $service->translation = $service->translations
                    ->where('locale', $locale)
                    ->first();

                $ruleToday = $service->ruleForDate($today);

                $service->effective_price = $service->effectivePriceForDate($today);
                $service->effective_duration_minutes = $ruleToday?->duration_minutes ?? $service->duration_minutes;

                $service->next_rule = $service->futureRules->sortBy('valid_from')->first();

                return $service;
            });
        
        return view('pages.homepage', [
            'settings' => $this->getSettings(),
            'services' => $services,
            'socials'  => $this->getSocials(),
        ]);
    }
    public function gallery(): View
    {
        $locale = app()->getLocale();
        $services = Services::where('status', 1)->where('is_deleted', 0)->orderBy('id', 'asc')->get()->map(function ($service) use ($locale) {
            $service->translation = $service->translations()->where('locale', $locale)->first();
            return $service;
        });
        return view('pages.gallery.index', [
            'settings' => $this->getSettings(),
            'services' => $services,
        ]);
    }
    public function contacts(): View
    {
        $locale = app()->getLocale();
        $services = Services::where('status', 1)->where('is_deleted', 0)->orderBy('id', 'asc')->get()->map(function ($service) use ($locale) {
            $service->translation = $service->translations()->where('locale', $locale)->first();
            return $service;
        });
        return view('pages.contacts', [
            'settings' => $this->getSettings(),
            'services' => $services,
            'socials' => $this->getSocials()
        ]);
    }

    function getSettings(){
        $siteSettings = SiteSettings::whereIn('group', ['company', 'branding', 'booking'])->get();
        if ($siteSettings) {
            $formattedSettings = $siteSettings->pluck('payload', 'key')->map(function ($value) {
                return json_decode($value, true);
            })->toArray();

            foreach (['logo', 'footer_logo', 'favicon'] as $key) {
                if (!empty($formattedSettings[$key])) {
                    $formattedSettings[$key.'_url'] = Storage::disk('public')->url($formattedSettings[$key]);
                }
            }
            $locale = app()->getLocale();
            $descriptions = $formattedSettings['company_short_description'] ?? [];
            $formattedSettings['company_short_description'] = is_array($descriptions)
                ? ($descriptions[$locale] ?? $descriptions[config('app.fallback_locale')] ?? '')
                : '';
    
            return $formattedSettings;
        }
    
        return [];
    }
    function getSocials(){
        $siteSettings = SiteSettings::where('group', 'social_media')->get();
        if ($siteSettings) {
            $formattedSettings = $siteSettings->pluck('payload', 'key')->map(function ($value) {
                return trim($value, '"'); 
            })->toArray();
    
            return $formattedSettings;
        }
    
        return [];
    }
    
    public function policy(): View
    {
        $locale = app()->getLocale();
        $services = Services::where('status', 1)->where('is_deleted', 0)->orderBy('id', 'asc')->get()->map(function ($service) use ($locale) {
            $service->translation = $service->translations()->where('locale', $locale)->first();
            return $service;
        });
        
        return view('pages.policy', [
            'settings' => $this->getSettings(),
            'services' => $services,
            'socials' => $this->getSocials()
        ]);
    }
}
