<?php

namespace App\Services\Booking;

use App\Models\AppointmentSlotHold;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class AppointmentSlotHoldService
{
    public const MINUTES = 5;

    public function __construct(
        private readonly SlotAvailabilityService $availability,
        private readonly RoomAllocationService $rooms,
    ) {
    }

    public function hold(User $actor, int $serviceId, int $userId, string $startValue, string $endValue, ?string $token = null): AppointmentSlotHold
    {
        return DB::transaction(function () use ($actor, $serviceId, $userId, $startValue, $endValue, $token) {
            $start = Carbon::parse($startValue)->setSecond(0);
            $end = Carbon::parse($endValue)->setSecond(0);
            if (! $start->isSameDay($end) || $start->isPast()) {
                throw new RuntimeException(__('admin_appointment_create.hold_invalid'));
            }

            $existing = $token
                ? AppointmentSlotHold::whereKey($token)->where('held_by_user_id', $actor->id)->lockForUpdate()->first()
                : null;
            $existing?->delete();

            User::whereKey($userId)->lockForUpdate()->firstOrFail();
            AppointmentSlotHold::where('expires_at', '<=', now())->delete();

            $date = $start->toDateString();
            $startTime = $start->format('H:i');
            $endTime = $end->format('H:i');
            if (! $this->availability->containsSlot($date, $serviceId, $userId, $startTime, $endTime)) {
                throw new RuntimeException(__('admin_appointment_create.hold_conflict'));
            }

            $roomId = $this->rooms->assign($userId, $date, $startTime, $endTime);
            if ($roomId === null && User::whereKey($userId)->whereHas('rooms')->exists()) {
                throw new RuntimeException(__('admin_appointment_create.hold_conflict'));
            }

            return AppointmentSlotHold::create([
                'token' => (string) Str::uuid(),
                'service_id' => $serviceId,
                'user_id' => $userId,
                'room_id' => $roomId,
                'held_by_user_id' => $actor->id,
                'appointment_start' => $start,
                'appointment_end' => $end,
                'expires_at' => now()->addMinutes(self::MINUTES),
            ]);
        }, 3);
    }

    public function renew(User $actor, string $token): AppointmentSlotHold
    {
        return DB::transaction(function () use ($actor, $token) {
            $hold = AppointmentSlotHold::whereKey($token)->where('held_by_user_id', $actor->id)->lockForUpdate()->firstOrFail();
            if ($hold->expires_at->isPast()) {
                $hold->delete();
                throw new RuntimeException(__('admin_appointment_create.hold_expired'));
            }
            $hold->update(['expires_at' => now()->addMinutes(self::MINUTES)]);

            return $hold->refresh();
        });
    }

    public function release(User $actor, string $token): void
    {
        AppointmentSlotHold::whereKey($token)->where('held_by_user_id', $actor->id)->delete();
    }

    public function lockValid(User $actor, string $token, int $userId, string $start, string $end): AppointmentSlotHold
    {
        $hold = AppointmentSlotHold::whereKey($token)->lockForUpdate()->first();
        if (! $hold || (int) $hold->held_by_user_id !== (int) $actor->id || (int) $hold->user_id !== $userId
            || $hold->expires_at->isPast() || ! $hold->appointment_start->equalTo(Carbon::parse($start))
            || ! $hold->appointment_end->equalTo(Carbon::parse($end))) {
            throw new RuntimeException(__('admin_appointment_create.hold_expired'));
        }

        return $hold;
    }
}
