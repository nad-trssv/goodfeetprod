<?php

namespace App\Services\Crm;

use App\Models\SiteSettings;
use App\Services\Localization\SiteLocaleRegistry;
use Carbon\Carbon;

class CrmChatSettings
{
    public function all(): array
    {
        $stored = SiteSettings::where('group', 'crm_chat')->pluck('payload', 'key');
        $value = fn (string $key, mixed $default) => $stored->has('crm_chat_'.$key)
            ? json_decode($stored['crm_chat_'.$key], true) : $default;
        $locale = app()->getLocale();
        $fallback = app(SiteLocaleRegistry::class)->defaultCode();
        $localized = function (string $key, string $default) use ($value, $locale, $fallback): array {
            $translations = $value($key, [$fallback => $default]);
            if (! is_array($translations)) {
                $translations = [$fallback => (string) $translations];
            }
            $selected = $translations[$locale] ?? null;
            if (! filled($selected)) {
                $selected = $translations[$fallback] ?? collect($translations)->first(fn ($text) => filled($text)) ?? $default;
            }

            return [
                'value' => (string) $selected,
                'translations' => $translations,
            ];
        };
        $title = $localized('title', 'Online chat');
        $welcome = $localized('welcome_message', 'Hello! How can we help you?');
        $offline = $localized('offline_message', 'We are currently offline. Leave a message and we will reply during working hours.');

        return [
            'enabled'=>(bool)$value('enabled', false),
            'title'=>$title['value'],
            'title_translations'=>$title['translations'],
            'welcome_message'=>$welcome['value'],
            'welcome_message_translations'=>$welcome['translations'],
            'offline_message'=>$offline['value'],
            'offline_message_translations'=>$offline['translations'],
            'notify_client_staff_events'=>(bool)$value('notify_client_staff_events', true),
            'timezone'=>(string)$value('timezone', config('app.timezone')),
            'schedule'=>(array)$value('schedule', []),
        ];
    }

    public function publicState(): array
    {
        $settings = $this->all();
        unset(
            $settings['title_translations'],
            $settings['welcome_message_translations'],
            $settings['offline_message_translations'],
        );
        $now = Carbon::now($settings['timezone']);
        $day = strtolower($now->englishDayOfWeek);
        $hours = $settings['schedule'][$day] ?? ['enabled'=>false];
        $online = $settings['enabled'] && ($hours['enabled'] ?? false)
            && $now->format('H:i') >= ($hours['start'] ?? '00:00')
            && $now->format('H:i') < ($hours['end'] ?? '00:00');

        return $settings + [
            'online'=>$online,
            'today_start'=>$hours['start'] ?? null,
            'today_end'=>$hours['end'] ?? null,
        ];
    }
}
