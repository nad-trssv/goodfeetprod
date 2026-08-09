<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->where('slug', 'customer')->update([
            'appointment_scope' => 'own',
            'is_service_provider' => false,
        ]);
    }

    public function down(): void
    {
        // Customer accounts never use the staff appointment scope.
    }
};
