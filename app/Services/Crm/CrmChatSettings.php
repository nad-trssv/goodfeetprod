<?php

namespace App\Services\Crm;

use App\Models\SiteSettings;
use Carbon\Carbon;

class CrmChatSettings
{
    public function all(): array
    {
        $stored = SiteSettings::where('group', 'crm_chat')->pluck('payload', 'key');
        $value = fn (string $key, mixed $default) => $stored->has('crm_chat_'.$key)
            ? json_decode($stored['crm_chat_'.$key], true) : $default;

        return [
            'enabled'=>(bool)$value('enabled', false),
            'title'=>(string)$value('title', 'Online chat'),
            'welcome_message'=>(string)$value('welcome_message', 'Hello! How can we help you?'),
            'offline_message'=>(string)$value('offline_message', 'We are currently offline. Leave a message and we will reply during working hours.'),
            'notify_client_staff_events'=>(bool)$value('notify_client_staff_events', true),
            'timezone'=>(string)$value('timezone', config('app.timezone')),
            'schedule'=>(array)$value('schedule', []),
        ];
    }

    public function publicState(): array
    {
        $settings = $this->all();
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
