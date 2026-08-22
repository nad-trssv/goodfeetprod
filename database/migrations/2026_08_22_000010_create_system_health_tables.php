<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_health_runs', function (Blueprint $table) {
            $table->id();
            $table->string('status', 16)->default('running');
            $table->string('source', 16)->default('scheduled');
            $table->unsignedInteger('duration_ms')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['started_at', 'status']);
        });

        Schema::create('system_health_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_health_run_id')
                ->constrained('system_health_runs')
                ->cascadeOnDelete();
            $table->string('key', 64);
            $table->string('status', 16);
            $table->unsignedInteger('duration_ms')->default(0);
            $table->string('message_key');
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['key', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_health_checks');
        Schema::dropIfExists('system_health_runs');
    }
};
