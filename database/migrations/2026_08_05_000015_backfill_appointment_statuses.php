<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('appointments')
            ->where(fn ($query) => $query->whereNull('status')->orWhere('status', ''))
            ->update([
                'status' => 'confirmed',
                'status_changed_at' => DB::raw('COALESCE(status_changed_at, updated_at, created_at)'),
            ]);
    }

    public function down(): void {}
};
