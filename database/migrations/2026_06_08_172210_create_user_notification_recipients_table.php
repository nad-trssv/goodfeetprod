<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_id'); 
            $table->unsignedBigInteger('recipient_id'); 
            $table->timestamps();

            $table->foreign('master_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['master_id', 'recipient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notification_recipients');
    }
};
