<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $companyEmailPayload = DB::table('site_settings')->where('key', 'company_email')->value('payload');
        $companyEmail = is_string($companyEmailPayload) ? json_decode($companyEmailPayload, true) : null;
        $companyDomain = is_string($companyEmail) && str_contains($companyEmail, '@')
            ? mb_strtolower(substr(strrchr($companyEmail, '@'), 1))
            : null;
        $senderEmail = is_string($companyEmail)
            && filter_var($companyEmail, FILTER_VALIDATE_EMAIL)
            && $companyDomain
            && str_contains($companyDomain, '.')
            && ! in_array($companyDomain, config('technical_support.free_email_domains', []), true)
                ? $companyEmail
                : null;

        DB::table('site_settings')->updateOrInsert(
            ['key' => 'technical_support_sender_email'],
            [
                'group' => 'company',
                'payload' => json_encode($senderEmail),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('site_settings')->where('key', 'technical_support_sender_email')->delete();
    }
};
