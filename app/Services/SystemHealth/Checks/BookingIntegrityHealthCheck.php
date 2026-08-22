<?php

namespace App\Services\SystemHealth\Checks;

use App\Models\Appointments;
use App\Services\SystemHealth\Contracts\SystemHealthCheck;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class BookingIntegrityHealthCheck extends AbstractHealthCheck implements SystemHealthCheck
{
    public function key(): string
    {
        return 'booking_integrity';
    }

    public function run(): \App\Services\SystemHealth\HealthCheckResult
    {
        return $this->probe(function (): array {
            foreach (['appointments', 'services', 'users'] as $table) {
                if (! Schema::hasTable($table)) {
                    throw new RuntimeException('A booking table is missing.');
                }
            }

            $orphans = DB::table('appointments')
                ->leftJoin('services', 'services.id', '=', 'appointments.service_id')
                ->leftJoin('users', 'users.id', '=', 'appointments.user_id')
                ->where(function ($query) {
                    $query->whereNull('services.id')->orWhereNull('users.id');
                })
                ->count();

            $duplicateSlots = 0;
            if (Schema::hasColumn('appointments', 'slot_lock_key')) {
                $duplicateSlots = DB::table('appointments')
                    ->whereIn('status', Appointments::BLOCKING_STATUSES)
                    ->whereNotNull('slot_lock_key')
                    ->select('slot_lock_key')
                    ->groupBy('slot_lock_key')
                    ->havingRaw('COUNT(*) > 1')
                    ->get()
                    ->count();
            }

            $context = ['orphans' => $orphans, 'duplicateSlots' => $duplicateSlots];
            if ($orphans > 0 || $duplicateSlots > 0) {
                return ['status' => 'failing', 'message_key' => 'admin_system_health.messages.booking_invalid', 'context' => $context];
            }

            return ['message_key' => 'admin_system_health.messages.booking_ok', 'context' => $context];
        });
    }
}
