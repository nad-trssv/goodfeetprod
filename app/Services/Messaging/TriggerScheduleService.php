<?php

namespace App\Services\Messaging;

use App\Models\AppointmentFeedback;
use App\Models\Appointments;
use App\Models\MessagingTriggerSetting;
use App\Models\TriggerMessageDispatch;
use App\Services\Localization\SiteLocaleRegistry;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class TriggerScheduleService
{
    public function syncCurrentAppointments(): int
    {
        $count = 0;
        Appointments::query()
            ->where('appointment_end', '>=', now()->subHours(12))
            ->whereIn('status', array_merge(Appointments::BLOCKING_STATUSES, ['completed']))
            ->orderBy('id')
            ->chunkById(200, function ($appointments) use (&$count) {
                foreach ($appointments as $appointment) {
                    $this->sync($appointment);
                    $count++;
                }
            });

        return $count;
    }

    public function sync(Appointments $appointment, bool $created = false): void
    {
        if (! Schema::hasTable('messaging_trigger_settings') || ! Schema::hasColumn('appointments', 'client_locale')) {
            return;
        }
        $appointment->refresh();
        $this->ensureLocale($appointment);
        $settings = MessagingTriggerSetting::query()->get()->keyBy('trigger');

        if ($created && ($setting = $settings->get('booking_created'))) {
            $oneDayFromNow = now()->addDay();
            $this->schedule(
                $appointment,
                'booking_created',
                $appointment->created_at ?? now(),
                $appointment->appointment_start->lt($oneDayFromNow) ? $appointment->appointment_start : $oneDayFromNow,
            );
        }

        if ($setting = $settings->get('appointment_reminder')) {
            $this->schedule(
                $appointment,
                'appointment_reminder',
                $appointment->appointment_start->copy()->subMinutes($setting->timing_minutes),
                $appointment->appointment_start,
            );
        }

        if ($setting = $settings->get('review_request')) {
            AppointmentFeedback::firstOrCreate(
                ['appointment_id' => $appointment->id],
                ['token' => (string) Str::uuid()],
            );
            $this->schedule(
                $appointment,
                'review_request',
                $appointment->appointment_end->copy()->addMinutes($setting->timing_minutes),
                $appointment->appointment_end->copy()->addHours(12),
            );
        }

        if (in_array($appointment->status, ['cancelled_by_client', 'cancelled_by_business', 'rescheduled'], true)) {
            $appointment->triggerDispatches()
                ->whereIn('trigger', ['appointment_reminder', 'review_request'])
                ->whereIn('status', ['pending', 'processing'])
                ->update(['status' => 'skipped', 'last_error' => 'Appointment is no longer active']);
        }
    }

    private function schedule(Appointments $appointment, string $trigger, $scheduledAt, $expiresAt): void
    {
        $dispatch = TriggerMessageDispatch::firstOrNew([
            'appointment_id' => $appointment->id,
            'trigger' => $trigger,
        ]);
        if ($dispatch->exists && $dispatch->status !== 'pending') {
            return;
        }
        $dispatch->fill([
            'status' => 'pending',
            'scheduled_at' => $scheduledAt,
            'expires_at' => $expiresAt,
            'last_error' => null,
        ])->save();
    }

    private function ensureLocale(Appointments $appointment): void
    {
        $registry = app(SiteLocaleRegistry::class);
        $locale = $appointment->client_locale ?: $appointment->customer?->locale;
        if (! array_key_exists((string) $locale, $registry->installedLabels())) {
            $locale = $registry->defaultCode();
        }
        if ($appointment->client_locale !== $locale) {
            $appointment->withoutEvents(fn () => $appointment->update(['client_locale' => $locale]));
        }
    }
}
