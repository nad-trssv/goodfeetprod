<?php

namespace App\Services;

use App\Models\RedDay;
use App\Models\SiteSettings;
use Illuminate\Support\Facades\Storage;
use App\Services\Localization\SiteLocaleRegistry;

class SettingService
{
    public $setting;

    public function list(): array
    {
        return [
            'workHours' => $this->decodedSetting('hours', 'work_hours'),
            'lunchHours' => $this->decodedSetting('hours', 'lunch_hours'),
            'bookingLimit' => $this->decodedSetting('hours', 'booking_date_limit'),
        ];
    }

    private function decodedSetting(string $group, string $key): mixed
    {
        $payload = SiteSettings::query()
            ->where('group', $group)
            ->where('key', $key)
            ->value('payload');

        return is_string($payload) ? json_decode($payload, true) : null;
    }
    public function updateHours($request)
    {
        $siteSettings = SiteSettings::where('group', 'hours')
                            ->where('key', 'work_hours')
                            ->first();
                            
        if ($siteSettings) {             
            $workHours = json_decode($siteSettings->payload, true);       
            $workHours = [
                'monday' => ['start' => $request->monday_start, 'end' => $request->monday_end],
                'tuesday' => ['start' => $request->tuesday_start, 'end' => $request->tuesday_end],
                'wednesday' => ['start' => $request->wednesday_start, 'end' => $request->wednesday_end],
                'thursday' => ['start' => $request->thursday_start, 'end' => $request->thursday_end],
                'friday' => ['start' => $request->friday_start, 'end' => $request->friday_end],
                'saturday' => ['start' => $request->saturday_start, 'end' => $request->saturday_end],
                'sunday' => ['start' => $request->sunday_start, 'end' => $request->sunday_end],
            ];      
            $siteSettings->payload = json_encode($workHours);
            $this->setting = $siteSettings->save();
            return $this->setting;
        }
    }

    public function updateLunchHours($request)
    {
        $lunchTimeSettings = SiteSettings::where('group', 'hours')->where('key', 'lunch_hours')->first();
        if ($lunchTimeSettings) { 
            $lunchHours = json_decode($lunchTimeSettings->payload, true); 
            $lunchHours = ['start' => $request->lunch_start, 'end' => $request->lunch_end];
            $lunchTimeSettings->payload = json_encode($lunchHours);
            $this->setting = $lunchTimeSettings->save();
            return $this->setting;
        }  
    }
    
    public function storeRedDay($request)
    {
        $full_day = false;
        if ($request['start_time'] == null || $request['end_time'] == null) {
            $full_day = true;
        }

        $this->setting = RedDay::create([
            'name' => $request['name'],
            'description' => $request['description'],
            'date' => $request['date'],
            'date_to' => $request['date_to'] ?: $request['date'],
            'type' => array_key_exists((string) $request['type'], RedDay::TYPES) ? $request['type'] : ($request['user_id'] ? 'other' : 'company_closure'),
            'start_time' => $request['start_time'],
            'end_time' => $request['end_time'],
            'full_day' => $full_day,
            'repeat' => $request['repeat'],
            'user_id' => $request['user_id'] ?? null,
        ]);

        return $this->setting;
    }

    public function updateFixedBooking($request)
    {
        $fixedBookingSettings = SiteSettings::where('group', 'hours')->where('key', 'fixed_booking_hours')->first();
        if ($fixedBookingSettings) { 
            $hours = $request['fixedBooking'];
            $status = $request['fixedBookingStatus'];

            $data = [
                "value" => $status,
                "payload" => $hours
            ];
            $fixedBookingSettings->payload = $data;
            $this->setting = $fixedBookingSettings->save();
    
            return $this->setting;
        }  
    }

    public function updateMainSettings($request)
    {
        $groups = [
            'google' => 'map', 'waze' => 'map',
            'social_media_facebook' => 'social_media', 'social_media_youtube' => 'social_media',
            'social_media_instagram' => 'social_media', 'social_media_twitter' => 'social_media',
            'logo' => 'branding', 'footer_logo' => 'branding', 'favicon' => 'branding',
            'primary_accent_color' => 'admin_branding',
            'booking_template' => 'booking',
            'show_service_images' => 'booking', 'show_master_images' => 'booking',
            'cancellation_notice_hours' => 'booking',
            'allow_customer_cancellation' => 'booking',
        ];

        foreach ($request->safe()->except(['logo', 'footer_logo', 'favicon']) as $key => $value) {
            if (in_array($key, ['show_service_images', 'show_master_images', 'allow_customer_cancellation'], true)) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            if ($key === 'company_short_description') {
                $value = collect($value ?? [])->mapWithKeys(function ($html, $locale) {
                    if (!array_key_exists($locale, app(SiteLocaleRegistry::class)->installedLabels())) {
                        return [];
                    }

                    return [$locale => strip_tags((string) $html, '<p><br><strong><b><em><i><u><ul><ol><li>')];
                })->all();
            }

            SiteSettings::updateOrCreate(
                ['key' => $key],
                ['group' => $groups[$key] ?? 'company', 'payload' => json_encode($value, JSON_UNESCAPED_UNICODE)]
            );
        }

        foreach (['logo', 'footer_logo', 'favicon'] as $key) {
            if (!$request->hasFile($key)) {
                continue;
            }

            $setting = SiteSettings::where('key', $key)->first();
            $oldPath = $setting ? json_decode($setting->payload, true) : null;
            $path = $request->file($key)->store('branding', 'public');

            SiteSettings::updateOrCreate(
                ['key' => $key],
                ['group' => 'branding', 'payload' => json_encode($path, JSON_UNESCAPED_UNICODE)]
            );

            if (is_string($oldPath) && str_starts_with($oldPath, 'branding/') && $oldPath !== $path) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        return true;
    }
}
