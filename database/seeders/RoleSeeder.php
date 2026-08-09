<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Roles;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $definitions = [
            ['name' => 'Admin', 'slug' => 'super-admin', 'appointment_scope' => 'all', 'is_service_provider' => true, 'is_system' => true],
            ['name' => 'Master', 'slug' => 'master', 'appointment_scope' => 'own', 'is_service_provider' => true, 'is_system' => true],
            ['name' => 'Klient', 'slug' => 'customer', 'appointment_scope' => 'own', 'is_service_provider' => false, 'is_system' => true],
            ['name' => 'Receptionist', 'slug' => 'receptionist', 'appointment_scope' => 'all', 'is_service_provider' => false, 'is_system' => true],
        ];

        foreach ($definitions as $definition) {
            $role = Roles::updateOrCreate(['name' => $definition['name']], $definition);

            $keys = config('role_defaults.'.$definition['slug'].'.permissions', []);

            if (Schema::hasTable('permissions')) {
                $role->permissions()->sync(Permission::whereIn('key', $keys)->pluck('id')->all());
            }
        }
    }
}
