<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('events')->insert([
            [
                'name' => 'Happy Birthday',
                'date' => '2025-01-20', 
                'description' => 'Поздравляем С Днем рождения!', 
                'user_id' => 2, 
                'repeat' => true, 
                'start_time' => null,
                'end_time' => null,
                'organized_by' => 1
            ],
            [
                'name' => 'Meeting',
                'date' => '2025-12-29', 
                'description'=> 'Просим всех явиться в 10:00 в кабинет номер 3 для совещания!', 
                'user_id' => null, 
                'repeat' => false, 
                'start_time' => '10:00',
                'end_time' => '11:30',
                'organized_by' => 1
            ],
        ]);
    }
}
