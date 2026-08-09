<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('appointments')->whereNull('customer_id')
            ->whereNotNull('client_email')->whereNotNull('client_phone')
            ->orderBy('id')->chunkById(100, function ($appointments) {
                foreach ($appointments as $appointment) {
                    $email = mb_strtolower(trim((string) $appointment->client_email));
                    $phone = preg_replace('/[^0-9+]/', '', trim((string) $appointment->client_phone));
                    $phone = str_starts_with($phone, '+') ? $phone : '+'.ltrim($phone, '+');
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($phone) < 8) continue;

                    $byEmail = DB::table('customers')->where('email', $email)->first();
                    $byPhone = DB::table('customers')->where('phone', $phone)->first();
                    if ($byEmail && $byPhone && $byEmail->id !== $byPhone->id) continue;

                    $customerId = $byEmail?->id ?? $byPhone?->id;
                    if ($customerId) {
                        DB::table('appointments')->where('id', $appointment->id)->update(['customer_id' => $customerId]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Historical identity reconciliation is intentionally irreversible.
    }
};
