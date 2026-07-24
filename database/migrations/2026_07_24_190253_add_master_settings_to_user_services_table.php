<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_services', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);

            $table->decimal('price_override', 10, 2)->nullable();

            $table->unsignedSmallInteger('duration_minutes_override')->nullable();

            $table->unsignedSmallInteger('duration_minutes_min_override')->nullable();

            $table->unsignedSmallInteger('buffer_before_minutes')->default(0);

            $table->unsignedSmallInteger('buffer_after_minutes')->default(0);
        });

        $duplicates = DB::table('user_services')
            ->select([
                'user_id',
                'service_id',
                DB::raw('MIN(id) AS keep_id'),
            ])
            ->groupBy('user_id', 'service_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('user_services')
                ->where('user_id', $duplicate->user_id)
                ->where('service_id', $duplicate->service_id)
                ->where('id', '<>', $duplicate->keep_id)
                ->delete();
        }

        Schema::table('user_services', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'service_id'],
                'user_services_user_service_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('user_services', function (Blueprint $table) {
            $table->dropUnique('user_services_user_service_unique');

            $table->dropColumn([
                'is_active',
                'price_override',
                'duration_minutes_override',
                'duration_minutes_min_override',
                'buffer_before_minutes',
                'buffer_after_minutes',
            ]);
        });
    }
};