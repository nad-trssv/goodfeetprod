<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messaging_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32)->unique();
            $table->boolean('enabled')->default(false);
            $table->json('settings')->nullable();
            $table->text('credentials')->nullable();
            $table->timestamps();
        });

        DB::table('site_settings')
            ->where('key', 'primary_accent_color')
            ->update(['group' => 'admin_branding']);
    }

    public function down(): void
    {
        Schema::dropIfExists('messaging_integrations');

        DB::table('site_settings')
            ->where('key', 'primary_accent_color')
            ->update(['group' => 'branding']);
    }
};
