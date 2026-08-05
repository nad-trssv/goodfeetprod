<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('users', fn (Blueprint $table) => $table->date('employment_started_at')->nullable()->after('date_birthday')); }
    public function down(): void { Schema::table('users', fn (Blueprint $table) => $table->dropColumn('employment_started_at')); }
};
