<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->string('event', 64);
            $table->nullableMorphs('actor');
            $table->string('reason', 500)->nullable();
            $table->json('changes')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['appointment_id', 'created_at']);
            $table->index(['event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_audits');
    }
};
