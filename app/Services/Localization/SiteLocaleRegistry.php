<?php

namespace App\Services\Localization;

use App\Models\Services;
use App\Models\SiteLocale;
use App\Models\SiteSettings;
use App\Models\SiteTranslation;
use App\Models\User;
use App\Models\MessagingTriggerSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SiteLocaleRegistry
{
    private ?Collection $installedCache = null;

    public function installed(): Collection
    {
        if ($this->installedCache !== null) {
            return $this->installedCache;
        }

        if (! Schema::hasTable('site_locales')) {
            return collect();
        }

        return $this->installedCache = SiteLocale::query()->orderBy('sort_order')->orderBy('code')->get();
    }

    public function active(): Collection
    {
        return $this->installed()->where('enabled', true)->values();
    }

    public function installedLabels(): array
    {
        return $this->installed()->mapWithKeys(fn (SiteLocale $locale) => [
            $locale->code => $locale->native_name,
        ])->all() ?: config('supported_locales', []);
    }

    public function activeLabels(): array
    {
        return $this->active()->mapWithKeys(fn (SiteLocale $locale) => [
            $locale->code => $locale->native_name,
        ])->all() ?: config('supported_locales', []);
    }

    public function defaultCode(): string
    {
        return $this->installed()->firstWhere('is_default', true)?->code
            ?? config('app.locale');
    }

    public function available(): array
    {
        return collect(config('site_localization.catalog', []))
            ->except($this->installed()->pluck('code')->all())
            ->all();
    }

    public function add(string $code, FrontendTranslationCatalog $catalogue): SiteLocale
    {
        $definition = config('site_localization.catalog.'.$code);
        abort_unless(is_array($definition), 422);
        $default = $this->defaultCode();

        return DB::transaction(function () use ($code, $definition, $default, $catalogue) {
            $locale = SiteLocale::create([
                'code' => $code,
                'name' => $definition['name'],
                'native_name' => $definition['native_name'],
                'enabled' => false,
                'is_default' => false,
                'sort_order' => ((int) SiteLocale::max('sort_order')) + 1,
            ]);
            $this->installedCache = null;

            $now = now();
            $rows = collect($catalogue->catalogue($default))->map(fn ($value, $key) => [
                'locale' => $code,
                'translation_key' => $key,
                'value' => $value,
                'is_inherited' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->values();
            $rows->chunk(250)->each(fn (Collection $chunk) => SiteTranslation::insert($chunk->all()));

            Services::query()->with('translations')->each(function (Services $service) use ($code, $default) {
                $source = $service->translations->firstWhere('locale', $default)
                    ?? $service->translations->first()
                    ?? (object) [
                        'name' => $service->name,
                        'short_description' => $service->short_description,
                        'full_description' => $service->full_description,
                    ];
                $service->translations()->updateOrCreate(['locale' => $code], [
                    'is_inherited' => true,
                    'name' => $source->name,
                    'short_description' => $source->short_description,
                    'full_description' => $source->full_description,
                ]);
            });

            User::query()->whereNotNull('professional_titles')->each(function (User $user) use ($code, $default) {
                $titles = $user->professional_titles ?? [];
                if (! array_key_exists($code, $titles) && filled($titles[$default] ?? null)) {
                    $titles[$code] = $titles[$default];
                    $user->forceFill(['professional_titles' => $titles])->save();
                }
            });

            $this->cloneLocalizedSetting('company_short_description', $code, $default);
            foreach (['crm_chat_title', 'crm_chat_welcome_message', 'crm_chat_offline_message'] as $key) {
                $this->cloneLocalizedSetting($key, $code, $default);
            }

            if (Schema::hasTable('messaging_trigger_settings')) {
                MessagingTriggerSetting::query()->each(function (MessagingTriggerSetting $setting) use ($code, $default) {
                    $templates = $setting->templates ?? [];
                    $templates[$code] = $templates[$default] ?? collect($templates)->first() ?? '';
                    $setting->update(['templates' => $templates]);
                });
            }

            return $locale;
        });
    }

    public function update(SiteLocale $locale, bool $enabled): SiteLocale
    {
        if ($locale->is_default && ! $enabled) {
            throw ValidationException::withMessages(['enabled' => __('admin_localization.default_cannot_disable')]);
        }

        $locale->update(['enabled' => $enabled]);
        $this->installedCache = null;

        return $locale->refresh();
    }

    public function makeDefault(SiteLocale $locale): SiteLocale
    {
        DB::transaction(function () use ($locale) {
            SiteLocale::query()->where('is_default', true)->update(['is_default' => false]);
            $locale->update(['is_default' => true, 'enabled' => true]);
        });
        $this->installedCache = null;

        return $locale->refresh();
    }

    public function statistics(SiteLocale $locale, FrontendTranslationCatalog $catalogue): array
    {
        $totalStrings = count($catalogue->catalogue($this->defaultCode()));
        $translatedStrings = SiteTranslation::query()
            ->where('locale', $locale->code)
            ->where('is_inherited', false)
            ->count();
        $serviceTotal = Services::query()->where('is_deleted', false)->count();
        $serviceTranslated = DB::table('service_translations')
            ->join('services', 'services.id', '=', 'service_translations.service_id')
            ->where('service_translations.locale', $locale->code)
            ->where('service_translations.is_inherited', false)
            ->where('services.is_deleted', false)
            ->count();

        return compact('totalStrings', 'translatedStrings', 'serviceTotal', 'serviceTranslated');
    }

    public function statisticsFor(Collection $locales, FrontendTranslationCatalog $catalogue): Collection
    {
        $codes = $locales->pluck('code');
        $totalStrings = count($catalogue->catalogue($this->defaultCode()));
        $translatedKeys = SiteTranslation::query()
            ->whereIn('locale', $codes)
            ->where('is_inherited', false)
            ->get(['locale', 'translation_key'])
            ->groupBy('locale');
        $serviceTotal = Services::query()->where('is_deleted', false)->count();
        $serviceTranslated = DB::table('service_translations')
            ->join('services', 'services.id', '=', 'service_translations.service_id')
            ->whereIn('service_translations.locale', $codes)
            ->where('service_translations.is_inherited', false)
            ->where('services.is_deleted', false)
            ->selectRaw('service_translations.locale, COUNT(*) as aggregate')
            ->groupBy('service_translations.locale')
            ->pluck('aggregate', 'service_translations.locale');

        $sourceKeys = array_keys($catalogue->catalogue($this->defaultCode()));

        return $locales->mapWithKeys(function (SiteLocale $locale) use ($catalogue, $sourceKeys, $totalStrings, $translatedKeys, $serviceTotal, $serviceTranslated) {
            $readyKeys = array_unique(array_merge(
                array_keys($catalogue->fileCatalogue($locale->code)),
                $translatedKeys->get($locale->code, collect())->pluck('translation_key')->all(),
            ));

            return [$locale->code => [
                'totalStrings' => $totalStrings,
                'translatedStrings' => count(array_intersect($sourceKeys, $readyKeys)),
                'serviceTotal' => $serviceTotal,
                'serviceTranslated' => (int) ($serviceTranslated[$locale->code] ?? 0),
            ]];
        });
    }

    private function cloneLocalizedSetting(string $key, string $newLocale, string $default): void
    {
        $setting = SiteSettings::query()->where('key', $key)->first();
        if (! $setting) {
            return;
        }

        $values = json_decode($setting->payload, true);
        if (! is_array($values)) {
            $values = [$default => (string) $values];
        }
        $values[$newLocale] = $values[$default] ?? collect($values)->first() ?? '';
        $setting->update(['payload' => json_encode($values, JSON_UNESCAPED_UNICODE)]);
    }
}
