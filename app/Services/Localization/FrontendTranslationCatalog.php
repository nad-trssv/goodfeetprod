<?php

namespace App\Services\Localization;

use App\Models\SiteTranslation;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class FrontendTranslationCatalog
{
    private array $catalogueCache = [];

    private array $fileCache = [];

    public function catalogue(string $locale): array
    {
        return $this->catalogueCache[$locale] ??= array_replace(
            $this->fileCatalogue($locale),
            SiteTranslation::query()
                ->where('locale', $locale)
                ->pluck('value', 'translation_key')
                ->all(),
        );
    }

    public function fileCatalogue(string $locale): array
    {
        if (array_key_exists($locale, $this->fileCache)) {
            return $this->fileCache[$locale];
        }

        $catalogue = [];

        foreach (config('site_localization.frontend_groups', []) as $group) {
            $path = lang_path($locale.'/'.$group.'.php');
            if (! is_file($path)) {
                continue;
            }

            $lines = require $path;
            if (is_array($lines)) {
                $this->flatten($lines, $group, $catalogue);
            }
        }

        foreach (config('site_localization.frontend_keys', []) as $translationKey) {
            [$group, $key] = explode('.', $translationKey, 2);
            $path = lang_path($locale.'/'.$group.'.php');
            if (! is_file($path)) {
                continue;
            }
            $lines = require $path;
            $value = data_get($lines, $key);
            if (is_string($value) || is_numeric($value)) {
                $catalogue[$translationKey] = (string) $value;
            }
        }

        ksort($catalogue);

        return $this->fileCache[$locale] = $catalogue;
    }

    public function rows(string $targetLocale, ?string $search = null, string $status = 'all'): Collection
    {
        $defaultLocale = app(SiteLocaleRegistry::class)->defaultCode();
        $source = $this->catalogue($defaultLocale);
        $targetFiles = $this->fileCatalogue($targetLocale);
        $target = array_replace($source, $targetFiles);
        $overrides = SiteTranslation::query()
            ->where('locale', $targetLocale)
            ->get()
            ->keyBy('translation_key');

        $needle = mb_strtolower(trim((string) $search));

        return collect($source)->map(function (string $sourceValue, string $key) use ($target, $targetFiles, $overrides) {
            $override = $overrides->get($key);

            return [
                'key' => $key,
                'source' => $sourceValue,
                'target' => $override?->value ?? ($target[$key] ?? $sourceValue),
                'inherited' => $override ? $override->is_inherited : ! array_key_exists($key, $targetFiles),
            ];
        })->filter(function (array $row) use ($needle, $status) {
            $matchesStatus = $status === 'all'
                || ($status === 'untranslated' && $row['inherited'])
                || ($status === 'translated' && ! $row['inherited']);

            return $matchesStatus && ($needle === ''
                || str_contains(mb_strtolower($row['key']), $needle)
                || str_contains(mb_strtolower($row['source']), $needle)
                || str_contains(mb_strtolower($row['target']), $needle));
        })->values();
    }

    public function save(string $locale, string $key, string $value): SiteTranslation
    {
        $defaultLocale = app(SiteLocaleRegistry::class)->defaultCode();
        $source = $this->catalogue($defaultLocale)[$key] ?? null;
        if ($source === null) {
            throw ValidationException::withMessages(['translation_key' => __('admin_localization.unknown_key')]);
        }

        $missing = array_diff($this->placeholders($source), $this->placeholders($value));
        if ($missing !== []) {
            throw ValidationException::withMessages([
                'value' => __('admin_localization.missing_placeholders', ['placeholders' => implode(', ', $missing)]),
            ]);
        }

        return SiteTranslation::updateOrCreate(
            ['locale' => $locale, 'translation_key' => $key],
            ['value' => $value, 'is_inherited' => false],
        );
    }

    private function flatten(array $lines, string $prefix, array &$catalogue): void
    {
        foreach ($lines as $key => $value) {
            $fullKey = $prefix.'.'.$key;
            if (is_array($value)) {
                $this->flatten($value, $fullKey, $catalogue);
            } elseif (is_string($value) || is_numeric($value)) {
                $catalogue[$fullKey] = (string) $value;
            }
        }
    }

    private function placeholders(string $value): array
    {
        preg_match_all('/:[A-Za-z_][A-Za-z0-9_]*/', $value, $matches);

        return array_values(array_unique($matches[0] ?? []));
    }
}
