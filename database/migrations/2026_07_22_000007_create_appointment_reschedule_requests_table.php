<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointment_reschedule_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_uuid')->unique();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->dateTime('old_start');
            $table->dateTime('old_end');
            $table->dateTime('requested_start');
            $table->dateTime('requested_end');
            $table->string('status', 20)->default('pending')->index();
            $table->string('reason', 500)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status', 'requested_start'], 'reschedule_master_pending_idx');
            $table->index(['room_id', 'status', 'requested_start'], 'reschedule_room_pending_idx');
        });
    }

    public function down(): void { Schema::dropIfExists('appointment_reschedule_requests'); }
};
