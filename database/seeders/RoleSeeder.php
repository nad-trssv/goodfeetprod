<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Roles;

class RoleSeeder extends Seeder
{
    public function run()
    {
        foreach (['Admin', 'Master', 'Klient'] as $role) {
            Roles::firstOrCreate(['name' => $role]);
        }
    }
}
