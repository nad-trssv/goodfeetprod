<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('public_uuid')->constrained()->nullOnDelete();
            $table->string('status', 32)->default('confirmed')->after('customer_id')->index();
            $table->timestamp('status_changed_at')->nullable()->after('status');
            $table->index(['customer_id', 'appointment_start']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'appointment_start']);
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'status', 'status_changed_at']);
        });
    }
};
