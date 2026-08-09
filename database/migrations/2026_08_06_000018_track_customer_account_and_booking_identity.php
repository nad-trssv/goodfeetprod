<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->timestamp('account_registered_at')->nullable()->after('password');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('customer_identity_verified')->default(false)->after('customer_id');
        });

        // Old accounts did not store the registration moment separately. Their
        // latest profile update is the safest available approximation.
        DB::table('customers')
            ->whereNotNull('password')
            ->update(['account_registered_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('customer_identity_verified');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('account_registered_at');
        });
    }
};
