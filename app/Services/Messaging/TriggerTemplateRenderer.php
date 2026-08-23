<?php

namespace App\Services\Messaging;

use App\Models\Appointments;
use App\Models\MessagingTriggerSetting;
use App\Models\SiteSettings;
use App\Services\Localization\SiteLocaleRegistry;

class TriggerTemplateRenderer
{
    public function locale(Appointments $appointment): string
    {
        $available = app(SiteLocaleRegistry::class)->installedLabels();
        $requested = $appointment->client_locale ?: $appointment->customer?->locale;

        return array_key_exists((string) $requested, $available)
            ? (string) $requested
            : app(SiteLocaleRegistry::class)->defaultCode();
    }

    public function render(MessagingTriggerSetting $setting, Appointments $appointment): string
    {
        $locale = $this->locale($appointment);
        $templates = $setting->templates ?? [];
        $template = (string) ($templates[$locale]
            ?? $templates[app(SiteLocaleRegistry::class)->defaultCode()]
            ?? collect($templates)->first()
            ?? '');
        $appointment->loadMissing(['service.translations', 'user', 'feedback']);
        $date = $appointment->appointment_start->copy()->locale($locale)->translatedFormat('d F Y');

        return strtr($template, [
            '{client_name}' => trim($appointment->client_name.' '.$appointment->client_lastname),
            '{service}' => $appointment->service?->getTranslation($locale, 'name') ?: $appointment->service?->name ?: '',
            '{master}' => $appointment->user?->name ?: '',
            '{date}' => $date,
            '{time}' => $appointment->appointment_start->format('H:i'),
            '{company}' => $this->companyName(),
            '{feedback_url}' => $appointment->feedback ? route('appointment-feedback.show', $appointment->feedback) : '',
        ]);
    }

    private function companyName(): string
    {
        $payload = SiteSettings::query()->where('key', 'company_name')->value('payload');
        $decoded = $payload !== null ? json_decode($payload, true) : null;

        return is_string($decoded) && $decoded !== '' ? $decoded : config('app.name');
    }
}
