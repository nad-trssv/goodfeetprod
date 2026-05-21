<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_schedules', function (Blueprint $table) {
            $table->boolean('fixed_booking_enabled')->default(false)->after('lunch_end');
            $table->json('fixed_booking_slots')->nullable()->after('fixed_booking_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('user_schedules', function (Blueprint $table) {
            $table->dropColumn(['fixed_booking_enabled', 'fixed_booking_slots']);
        });
    }
};