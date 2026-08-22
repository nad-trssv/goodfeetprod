<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_conversations', function (Blueprint $table) {
            $table->foreignId('previous_conversation_id')
                ->nullable()
                ->unique()
                ->after('customer_id')
                ->constrained('crm_conversations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('crm_conversations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('previous_conversation_id');
        });
    }
};
