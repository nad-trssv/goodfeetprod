<?php

namespace App\Http\Controllers;

use App\Models\Services;
use App\Models\SiteSettings;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
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

        return view('pages.service.index', [
            'services' => $services,
            'settings' => $this->getSettings(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    function getSettings(){
        $siteSettings = SiteSettings::whereIn('group', ['company', 'booking'])->get();
        if ($siteSettings) {
            $formattedSettings = $siteSettings->pluck('payload', 'key')->map(function ($value) {
                return json_decode($value, true);
            })->toArray();
    
            return $formattedSettings;
        }
    
        return [];
    }
}
