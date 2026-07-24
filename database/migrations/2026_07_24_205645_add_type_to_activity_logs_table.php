<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            /*
             * 1 — информация
             * 2 — предупреждение
             * 3 — ошибка
             */
            $table->unsignedTinyInteger('type')
                ->default(1)
                ->after('id')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_type_index');
            $table->dropColumn('type');
        });
    }
};