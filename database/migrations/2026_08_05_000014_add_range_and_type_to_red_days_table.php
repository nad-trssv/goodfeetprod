<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('red_days',function(Blueprint $table){$table->date('date_to')->nullable()->after('date');$table->string('type',32)->default('other')->after('name');$table->index(['user_id','date','date_to']);}); }
 public function down(): void { Schema::table('red_days',function(Blueprint $table){$table->dropIndex(['user_id','date','date_to']);$table->dropColumn(['date_to','type']);}); }
};
