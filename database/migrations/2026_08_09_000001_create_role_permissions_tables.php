<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('appointment_scope', 16)->default('own')->after('slug');
            $table->boolean('is_service_provider')->default(false)->after('appointment_scope');
            $table->boolean('is_system')->default(false)->after('is_service_provider');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group');
            $table->string('action', 32);
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        $now = now();
        foreach (config('permissions') as $key => $definition) {
            DB::table('permissions')->insert([
                'key' => $key,
                'group' => $definition['group'],
                'action' => $definition['action'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // A fresh test database intentionally stays empty until its test creates roles.
        // Existing installations already contain the three legacy roles.
        if (DB::table('roles')->count() === 0) {
            return;
        }

        $roles = DB::table('roles')->get();
        foreach ($roles as $role) {
            $normalized = Str::of($role->name)->lower()->replace([' ', '_'], '-')->toString();
            $slug = match (true) {
                in_array($normalized, ['admin', 'super-admin'], true) => 'super-admin',
                in_array($normalized, ['master', 'мастер'], true) => 'master',
                in_array($normalized, ['klient', 'client', 'customer', 'клиент'], true) => 'customer',
                default => Str::slug($role->name),
            };

            DB::table('roles')->where('id', $role->id)->update([
                'slug' => $slug ?: 'role-'.$role->id,
                'appointment_scope' => in_array($slug, ['master', 'customer'], true) ? 'own' : 'all',
                'is_service_provider' => in_array($slug, ['super-admin', 'master'], true),
                'is_system' => in_array($slug, ['super-admin', 'master', 'customer'], true),
            ]);
        }

        $receptionistId = DB::table('roles')->where('slug', 'receptionist')->value('id');
        if (! $receptionistId) {
            $receptionistId = DB::table('roles')->insertGetId([
                'name' => 'Receptionist',
                'slug' => 'receptionist',
                'appointment_scope' => 'all',
                'is_service_provider' => false,
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->assignDefaults($receptionistId, 'receptionist');

        foreach (DB::table('roles')->get() as $role) {
            if (in_array($role->slug, ['super-admin', 'master'], true)) {
                $this->assignDefaults((int) $role->id, $role->slug);
            }
        }
    }

    private function assignDefaults(int $roleId, string $slug): void
    {
        $keys = config('role_defaults.'.$slug.'.permissions', []);

        $permissionIds = DB::table('permissions')->whereIn('key', $keys)->pluck('id');
        foreach ($permissionIds as $permissionId) {
            DB::table('permission_role')->insertOrIgnore([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'appointment_scope', 'is_service_provider', 'is_system']);
        });
    }
};
