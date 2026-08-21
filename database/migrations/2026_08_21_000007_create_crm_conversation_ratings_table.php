<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_conversation_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->unique()->constrained('crm_conversations')->cascadeOnDelete();
            $table->foreignId('staff_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('staff_name', 190);
            $table->unsignedTinyInteger('rating');
            $table->timestamps();
            $table->index(['staff_user_id', 'rating'], 'crm_ratings_staff_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_conversation_ratings');
    }
};
