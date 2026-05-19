<?php

namespace App\Http\Controllers;

use App\Models\Services;
use App\Models\SiteSettings;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;

class PageController extends Controller
{
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
        $siteSettings = SiteSettings::where('group', 'company')->get();
        if ($siteSettings) {
            $formattedSettings = $siteSettings->pluck('payload', 'key')->map(function ($value) {
                return trim($value, '"');
            })->toArray();
    
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
