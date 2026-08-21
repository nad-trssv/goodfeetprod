<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_slot_holds', function (Blueprint $table) {
            $table->uuid('token')->primary();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('held_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('appointment_start');
            $table->dateTime('appointment_end');
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->index(['user_id', 'appointment_start', 'appointment_end'], 'slot_holds_master_interval');
            $table->index(['room_id', 'appointment_start', 'appointment_end'], 'slot_holds_room_interval');
            $table->index('expires_at');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->text('admin_notes')->nullable()->after('description');
            $table->uuid('series_uuid')->nullable()->after('admin_notes');
            $table->unsignedSmallInteger('series_sequence')->nullable()->after('series_uuid');
            $table->unsignedSmallInteger('series_total')->nullable()->after('series_sequence');
            $table->index(['series_uuid', 'series_sequence']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['series_uuid', 'series_sequence']);
            $table->dropColumn(['admin_notes', 'series_uuid', 'series_sequence', 'series_total']);
        });

        Schema::dropIfExists('appointment_slot_holds');
    }
};
