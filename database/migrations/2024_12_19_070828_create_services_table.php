<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('eventColor'); 
            $table->decimal('price', 10, 2);
            $table->boolean('price_can_change')->default(false);
            $table->integer('duration_minutes_min')->nullable()->default(null);
            $table->integer('duration_minutes');
            $table->text('short_description')->nullable()->default(null); 
            $table->text('full_description')->nullable()->default(null); 
            $table->boolean('status')->default(true);
            $table->time('time_from')->nullable()->default(null);
            $table->time('time_to')->nullable()->default(null);
            $table->boolean('has_fixed_time')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
