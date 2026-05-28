<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Берём глобальное рабочее время из site_settings
        $workHoursSetting = DB::table('site_settings')
            ->where('group', 'hours')
            ->where('key', 'work_hours')
            ->first();

        $lunchHoursSetting = DB::table('site_settings')
            ->where('group', 'hours')
            ->where('key', 'lunch_hours')
            ->first();

        if (!$workHoursSetting) {
            $this->command->info('work_hours не найдены в site_settings — пропускаем.');
            return;
        }

        $workHours = json_decode($workHoursSetting->payload, true);
        $lunchHours = $lunchHoursSetting ? json_decode($lunchHoursSetting->payload, true) : ['start' => null, 'end' => null];

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $scheduleData = ['user_id' => 1];

        foreach ($days as $day) {
            $scheduleData[$day . '_start'] = $workHours[$day]['start'] ?? null;
            $scheduleData[$day . '_end'] = $workHours[$day]['end'] ?? null;
        }

        $scheduleData['lunch_start'] = $lunchHours['start'] ?? null;
        $scheduleData['lunch_end'] = $lunchHours['end'] ?? null;
        $scheduleData['fixed_booking_enabled'] = 0;
        $scheduleData['fixed_booking_slots'] = json_encode([]);
        $scheduleData['created_at'] = now();
        $scheduleData['updated_at'] = now();

        // 2. Создаём или обновляем расписание для user_id=1
        $exists = DB::table('user_schedules')->where('user_id', 1)->first();

        if ($exists) {
            DB::table('user_schedules')->where('user_id', 1)->update($scheduleData);
            $this->command->info('Расписание для user_id=1 обновлено.');
        } else {
            DB::table('user_schedules')->insert($scheduleData);
            $this->command->info('Расписание для user_id=1 создано.');
        }

        // 3. Общие red_days (user_id IS NULL) оставляем как есть — они видны всем
        $commonCount = DB::table('red_days')->whereNull('user_id')->count();
        $this->command->info("Общих нерабочих дней (user_id=NULL): {$commonCount} — оставляем как есть.");

        // 4. Проверяем есть ли уже расписание у user_id=1
        $userSchedule = DB::table('user_schedules')->where('user_id', 1)->first();
        $this->command->info('Готово! Расписание мастера:');
        foreach ($days as $day) {
            $start = $userSchedule->{$day . '_start'} ?? 'выходной';
            $end = $userSchedule->{$day . '_end'} ?? '';
            $this->command->info("  {$day}: {$start}" . ($end ? " - {$end}" : ''));
        }
    }
}
