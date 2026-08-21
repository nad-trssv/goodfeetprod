<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_messages', function (Blueprint $table) {
            $table->string('event_type', 50)->nullable()->after('sender_type');
            $table->json('metadata')->nullable()->after('sender_id');
            $table->boolean('is_public')->default(true)->after('metadata');
            $table->index(['conversation_id', 'is_public', 'id'], 'crm_messages_public_poll');
        });

        DB::table('site_settings')->updateOrInsert(
            ['key' => 'crm_chat_notify_client_staff_events'],
            [
                'group' => 'crm_chat',
                'payload' => json_encode(true),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('site_settings')->where('key', 'crm_chat_notify_client_staff_events')->delete();

        Schema::table('crm_messages', function (Blueprint $table) {
            $table->dropIndex('crm_messages_public_poll');
            $table->dropColumn(['event_type', 'metadata', 'is_public']);
        });
    }
};
