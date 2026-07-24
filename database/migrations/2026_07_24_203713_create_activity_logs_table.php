<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            /*
             * Пользователь, который совершил действие.
             * Если мастер будет удалён, сам журнал останется.
             */
            $table->foreignId('actor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Имя сохраняется отдельно на момент действия.
             * Поэтому оно останется даже после удаления сотрудника.
             */
            $table->string('actor_name')->nullable();

            /*
             * Технический код события:
             * service.enabled
             * service.personal_settings.updated
             */
            $table->string('event', 100)->index();

            $table->string('module', 60)->index();

            $table->nullableMorphs('subject');

            $table->string('subject_name')->nullable();

            $table->string('message');

            $table->json('properties')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')
                ->useCurrent()
                ->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};