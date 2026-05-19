<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_id')
                ->constrained('services')
                ->onDelete('cascade');

            $table->date('valid_from');

            $table->date('valid_to')->nullable();

            $table->decimal('price', 10, 2)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->integer('duration_minutes_min')->nullable();

            $table->time('time_from')->nullable();
            $table->time('time_to')->nullable();
            $table->boolean('has_fixed_time')->default(false);

            $table->timestamps();

            $table->index(['service_id', 'valid_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_rules');
    }
};
