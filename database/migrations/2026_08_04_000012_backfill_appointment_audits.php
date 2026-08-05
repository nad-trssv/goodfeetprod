<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('appointments')
            ->whereNotExists(fn ($query) => $query->selectRaw('1')
                ->from('appointment_audits')
                ->whereColumn('appointment_audits.appointment_id', 'appointments.id'))
            ->orderBy('id')
            ->chunkById(500, function ($appointments) {
                $rows = $appointments->map(function ($appointment) {
                    $changes = [];
                    foreach (['status', 'service_id', 'user_id', 'room_id', 'appointment_start', 'appointment_end', 'price', 'original_price', 'discount_amount', 'promo_code', 'description'] as $field) {
                        $changes[$field] = $appointment->{$field};
                    }

                    return [
                        'appointment_id' => $appointment->id,
                        'event' => 'history_started',
                        'actor_type' => null,
                        'actor_id' => null,
                        'reason' => null,
                        'changes' => json_encode($changes, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        'context' => json_encode(['backfilled' => true], JSON_THROW_ON_ERROR),
                        'created_at' => $appointment->created_at ?? now(),
                        'updated_at' => now(),
                    ];
                })->all();

                if ($rows !== []) {
                    DB::table('appointment_audits')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        DB::table('appointment_audits')->where('event', 'history_started')->delete();
    }
};
