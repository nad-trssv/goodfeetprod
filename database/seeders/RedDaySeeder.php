<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RedDaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('red_days')->insert([
            [
                'date' => '2025-01-20', 
                'name' => 'New Year',
                'start_time' => null,
                'end_time' => null,
                'full_day' => 1,
                'repeat' => 1,
            ],
            [
                'date' => '2025-02-24', 
                'name' => 'Gos Prazdnik',
                'start_time' => '09:00',
                'end_time' => '12:00',
                'full_day' => 0,
                'repeat' => 0,
            ],
            [
                'date' => '2025-12-29', 
                'name' => 'Christmas',
                'start_time' => null,
                'end_time' => null,
                'full_day' => 1,
                'repeat' => 1,
            ],
        ]);
    }
}
