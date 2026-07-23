<?php

namespace App\View\Components;

use App\Models\SiteSettings;
use App\Models\Services;
use Illuminate\View\Component;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        
        return view('layouts.app', [
            'settings' => $this->getSettings(),
            'socials' => $this->getSocials(),
            'services' => $this->getServices(),
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

            $descriptions = $formattedSettings['company_short_description'] ?? [];
            $locale = app()->getLocale();
            $formattedSettings['company_short_description'] = is_array($descriptions)
                ? ($descriptions[$locale] ?? $descriptions[config('app.fallback_locale')] ?? '')
                : '';
    
            return $formattedSettings;
        }
    
        return [];
    }

    function getServices()
    {
        $locale = app()->getLocale();

        return Services::with('translations')
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->orderBy('id')
            ->get()
            ->map(function ($service) use ($locale) {
                $service->translation = $service->translations->firstWhere('locale', $locale);
                return $service;
            });
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
}
