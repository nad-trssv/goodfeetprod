<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up(): void {
  Schema::table('appointments', function(Blueprint $table){ $table->dropUnique('appointments_master_start_unique'); $table->string('slot_lock_key')->nullable()->after('status'); $table->unique('slot_lock_key','appointments_active_slot_unique'); });
  DB::table('appointments')->whereIn('status',['pending','confirmed','checked_in','in_progress'])->orderBy('id')->eachById(function($item){ DB::table('appointments')->where('id',$item->id)->update(['slot_lock_key'=>$item->user_id.'|'.date('Y-m-d H:i:s',strtotime($item->appointment_start))]); });
 }
 public function down(): void {
  Schema::table('appointments', function(Blueprint $table){ $table->dropUnique('appointments_active_slot_unique'); $table->dropColumn('slot_lock_key'); $table->unique(['user_id','appointment_start'],'appointments_master_start_unique'); });
 }
};
