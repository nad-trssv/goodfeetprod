<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->insertOrIgnore([
            'key' => 'dashboard.full',
            'group' => 'dashboard',
            'action' => 'full',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permissionId = DB::table('permissions')->where('key', 'dashboard.full')->value('id');
        $adminRoleIds = DB::table('roles')
            ->where(fn ($query) => $query
                ->where('slug', 'super-admin')
                ->orWhereIn(DB::raw('LOWER(name)'), ['admin', 'super admin', 'super-admin'])
                ->orWhere('id', 1))
            ->pluck('id');

        foreach ($adminRoleIds as $roleId) {
            DB::table('permission_role')->insertOrIgnore([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('key', 'dashboard.full')->value('id');

        if ($permissionId) {
            DB::table('permission_role')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
    }
};
