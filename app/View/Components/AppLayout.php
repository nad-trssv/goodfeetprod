<?php

namespace App\View\Components;

use App\Models\SiteSettings;
use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        
        return view('layouts.app', [
            'settings' => $this->getSettings(),
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
}
