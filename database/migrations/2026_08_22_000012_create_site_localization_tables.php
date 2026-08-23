<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_locales', function (Blueprint $table) {
            $table->string('code', 10)->primary();
            $table->string('name', 80);
            $table->string('native_name', 80);
            $table->boolean('enabled')->default(false);
            $table->boolean('is_default')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['enabled', 'sort_order']);
        });

        Schema::create('site_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 10);
            $table->string('translation_key', 190);
            $table->text('value');
            $table->boolean('is_inherited')->default(false);
            $table->timestamps();
            $table->unique(['locale', 'translation_key']);
            $table->foreign('locale')->references('code')->on('site_locales')->cascadeOnDelete();
            $table->index(['locale', 'is_inherited']);
        });

        Schema::table('service_translations', function (Blueprint $table) {
            $table->boolean('is_inherited')->default(false)->after('locale');
        });

        $now = now();
        $configured = config('supported_locales', []);
        $catalog = config('site_localization.catalog', []);
        $default = array_key_exists(config('app.locale'), $configured)
            ? config('app.locale')
            : (array_key_first($configured) ?: 'et');

        foreach ($configured as $code => $label) {
            DB::table('site_locales')->insert([
                'code' => $code,
                'name' => $catalog[$code]['name'] ?? $label,
                'native_name' => $catalog[$code]['native_name'] ?? $label,
                'enabled' => true,
                'is_default' => $code === $default,
                'sort_order' => array_search($code, array_keys($configured), true),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (['title', 'welcome_message', 'offline_message'] as $key) {
            $setting = DB::table('site_settings')->where('key', 'crm_chat_'.$key)->first();
            if (! $setting) {
                continue;
            }

            $decoded = json_decode($setting->payload, true);
            if (is_array($decoded)) {
                continue;
            }

            $localized = [];
            foreach (array_keys($configured) as $locale) {
                $localized[$locale] = (string) $decoded;
            }
            DB::table('site_settings')->where('id', $setting->id)->update([
                'payload' => json_encode($localized, JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        foreach (['title', 'welcome_message', 'offline_message'] as $key) {
            $setting = DB::table('site_settings')->where('key', 'crm_chat_'.$key)->first();
            if (! $setting) {
                continue;
            }
            $decoded = json_decode($setting->payload, true);
            if (is_array($decoded)) {
                DB::table('site_settings')->where('id', $setting->id)->update([
                    'payload' => json_encode($decoded[config('app.locale')] ?? reset($decoded) ?: ''),
                ]);
            }
        }

        Schema::table('service_translations', function (Blueprint $table) {
            $table->dropColumn('is_inherited');
        });
        Schema::dropIfExists('site_translations');
        Schema::dropIfExists('site_locales');
    }
};
