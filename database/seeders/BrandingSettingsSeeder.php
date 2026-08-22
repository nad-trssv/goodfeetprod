<?php

namespace Database\Seeders;

use App\Models\SiteSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BrandingSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $files = [
            'logo' => ['assets/img/logos/logo.svg', 'branding/defaults/header-logo.svg'],
            'footer_logo' => ['assets/img/logos/logo.svg', 'branding/defaults/footer-logo.svg'],
            'favicon' => ['assets/img/favicons/32x32.png', 'branding/defaults/favicon-32x32.png'],
        ];

        foreach ($files as $key => [$source, $target]) {
            if (!Storage::disk('public')->exists($target)) {
                Storage::disk('public')->put($target, file_get_contents(public_path($source)));
            }

            $setting = SiteSettings::where('key', $key)->first();
            $currentPath = $setting ? json_decode($setting->payload, true) : null;

            if (!$setting || !is_string($currentPath) || !str_starts_with($currentPath, 'branding/')) {
                SiteSettings::updateOrCreate(
                    ['key' => $key],
                    ['group' => 'branding', 'payload' => json_encode($target, JSON_UNESCAPED_UNICODE)]
                );
            }
        }

        SiteSettings::firstOrCreate(
            ['key' => 'company_short_description'],
            ['group' => 'company', 'payload' => json_encode(array_fill_keys(array_keys(config('supported_locales')), ''), JSON_UNESCAPED_UNICODE)]
        );

        SiteSettings::firstOrCreate(
            ['key' => 'booking_template'],
            ['group' => 'booking', 'payload' => json_encode('classic')]
        );

        SiteSettings::firstOrCreate(
            ['key' => 'primary_accent_color'],
            ['group' => 'admin_branding', 'payload' => json_encode('#3874FF')]
        );

        foreach (['show_service_images', 'show_master_images', 'allow_customer_cancellation'] as $key) {
            SiteSettings::firstOrCreate(
                ['key' => $key],
                ['group' => 'booking', 'payload' => json_encode(true)]
            );
        }
    }
}
