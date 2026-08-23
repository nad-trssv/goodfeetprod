<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'appointment_start'], 'appointments_user_status_start_idx');
            $table->index(['status', 'appointment_end'], 'appointments_status_end_idx');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_user_status_start_idx');
            $table->dropIndex('appointments_status_end_idx');
        });
    }
};
