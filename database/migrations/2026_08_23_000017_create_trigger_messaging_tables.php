<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('client_locale', 10)->nullable()->after('client_email')->index();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->json('messaging_contacts')->nullable()->after('locale');
        });

        Schema::table('messaging_integrations', function (Blueprint $table) {
            $table->unsignedTinyInteger('priority')->default(10)->after('enabled')->index();
        });

        DB::table('messaging_integrations')->where('provider', 'whatsapp')->update(['priority' => 1]);
        DB::table('messaging_integrations')->where('provider', 'viber')->update(['priority' => 2]);
        DB::table('messaging_integrations')->where('provider', 'telegram')->update(['priority' => 3]);

        Schema::create('messaging_trigger_settings', function (Blueprint $table) {
            $table->id();
            $table->string('trigger', 40)->unique();
            $table->boolean('enabled')->default(false);
            $table->unsignedInteger('timing_minutes')->default(0);
            $table->json('templates');
            $table->timestamps();
        });

        Schema::create('appointment_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained('appointments')->cascadeOnDelete();
            $table->uuid('token')->unique();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('trigger_message_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->string('trigger', 40);
            $table->string('status', 24)->default('pending');
            $table->timestamp('scheduled_at');
            $table->timestamp('expires_at')->nullable();
            $table->string('sent_provider', 32)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['appointment_id', 'trigger']);
            $table->index(['status', 'scheduled_at']);
        });

        Schema::create('trigger_message_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_id')->constrained('trigger_message_dispatches')->cascadeOnDelete();
            $table->string('provider', 32);
            $table->string('status', 24);
            $table->string('recipient', 190)->nullable();
            $table->string('external_id', 190)->nullable()->index();
            $table->text('error')->nullable();
            $table->json('response')->nullable();
            $table->timestamp('attempted_at');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['dispatch_id', 'provider']);
        });

        $now = now();
        DB::table('messaging_trigger_settings')->insert([
            [
                'trigger' => 'booking_created', 'enabled' => false, 'timing_minutes' => 0,
                'templates' => json_encode([
                    'ru' => 'Здравствуйте, {client_name}! Вы записаны на услугу «{service}» к специалисту {master}. Дата: {date}, время: {time}.',
                    'et' => 'Tere, {client_name}! Olete broneerinud teenuse „{service}“ spetsialisti {master} juurde. Kuupäev: {date}, kellaaeg: {time}.',
                    'en' => 'Hello, {client_name}! Your {service} appointment with {master} is booked for {date} at {time}.',
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'trigger' => 'appointment_reminder', 'enabled' => false, 'timing_minutes' => 1440,
                'templates' => json_encode([
                    'ru' => 'Напоминаем: завтра, {date}, в {time} у вас услуга «{service}» у специалиста {master}.',
                    'et' => 'Meeldetuletus: homme, {date}, kell {time} on teil teenus „{service}“ spetsialisti {master} juures.',
                    'en' => 'Reminder: tomorrow, {date}, at {time}, you have a {service} appointment with {master}.',
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'trigger' => 'review_request', 'enabled' => false, 'timing_minutes' => 120,
                'templates' => json_encode([
                    'ru' => 'Спасибо за визит! Оцените, пожалуйста, услугу по шкале от 1 до 5: {feedback_url}',
                    'et' => 'Täname külastuse eest! Palun hinnake teenust skaalal 1–5: {feedback_url}',
                    'en' => 'Thank you for visiting! Please rate your appointment from 1 to 5: {feedback_url}',
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('trigger_message_attempts');
        Schema::dropIfExists('trigger_message_dispatches');
        Schema::dropIfExists('appointment_feedback');
        Schema::dropIfExists('messaging_trigger_settings');

        Schema::table('messaging_integrations', function (Blueprint $table) {
            $table->dropIndex(['priority']);
            $table->dropColumn('priority');
        });
        Schema::table('customers', fn (Blueprint $table) => $table->dropColumn('messaging_contacts'));
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['client_locale']);
            $table->dropColumn('client_locale');
        });
    }
};
