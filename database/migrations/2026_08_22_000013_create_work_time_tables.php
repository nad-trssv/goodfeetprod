<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('started_at')->index();
            $table->dateTime('ended_at')->nullable()->index();
            $table->string('status', 16)->default('running')->index();
            $table->unsignedBigInteger('active_user_id')->nullable()->unique();
            $table->timestamps();

            $table->foreign('active_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'started_at']);
        });

        Schema::create('work_shift_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_shift_id')->constrained('work_shifts')->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->unsignedBigInteger('active_shift_id')->nullable()->unique();
            $table->timestamps();

            $table->foreign('active_shift_id')->references('id')->on('work_shifts')->cascadeOnDelete();
            $table->index(['work_shift_id', 'started_at']);
        });

        DB::table('permissions')->insertOrIgnore([
            'key' => 'work_time.view',
            'group' => 'work_time',
            'action' => 'view',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permissionId = DB::table('permissions')->where('key', 'work_time.view')->value('id');
        $adminRoleIds = DB::table('roles')->where('slug', 'super-admin')->pluck('id');
        foreach ($adminRoleIds as $roleId) {
            DB::table('permission_role')->insertOrIgnore([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('key', 'work_time.view')->value('id');
        if ($permissionId) {
            DB::table('permission_role')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }

        Schema::dropIfExists('work_shift_breaks');
        Schema::dropIfExists('work_shifts');
    }
};
