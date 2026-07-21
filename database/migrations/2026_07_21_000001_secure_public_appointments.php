<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', fn (Blueprint $table) => $table->uuid('public_uuid')->nullable()->after('id'));
        DB::table('appointments')->select('id')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) DB::table('appointments')->where('id', $row->id)->update(['public_uuid' => (string) Str::uuid()]);
        });
        Schema::table('appointments', function (Blueprint $table) {
            $table->unique('public_uuid', 'appointments_public_uuid_unique');
            $table->unique(['user_id', 'appointment_start'], 'appointments_master_start_unique');
        });
    }
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('appointments_master_start_unique');
            $table->dropUnique('appointments_public_uuid_unique');
            $table->dropColumn('public_uuid');
        });
    }
};
