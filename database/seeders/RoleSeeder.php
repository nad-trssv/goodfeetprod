<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run()
    {
        DB::table('roles')->insert([
            ['name' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Master', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Klient', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
