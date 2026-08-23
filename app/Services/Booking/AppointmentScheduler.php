<?php

namespace App\Services\Booking;

use App\Http\Requests\AppointmentRequest;
use App\Models\Appointments;
use App\Models\Customer;
use App\Models\Services;
use App\Models\User;
use App\Services\Customer\CustomerIdentityService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use App\Services\Localization\SiteLocaleRegistry;

class AppointmentScheduler
{
    public function __construct(
        private readonly SlotAvailabilityService $availability,
        private readonly RoomAllocationService $rooms,
        private readonly AppointmentNotificationService $notifications,
        private readonly AppointmentAuditService $audit,
        private readonly CustomerIdentityService $customers,
        private readonly AppointmentSlotHoldService $holds,
    ) {
    }

    public function create(AppointmentRequest $request): Appointments
    {
        return $this->createBatch($request)->firstOrFail();
    }

    /**
     * Create one appointment or a weekly series atomically. Every occurrence
     * is validated against the same server-side availability rules.
     */
    public function createBatch(AppointmentRequest $request): Collection
    {
        $appointments = DB::transaction(function () use ($request) {
            $repeatCount = (int) ($request->validated('repeat_count') ?: 1);
            $repeatInterval = (int) ($request->validated('repeat_interval_weeks') ?: 1);
            [$firstDate, $firstStart, $firstEnd] = $this->interval(
                $request->validated('appointment_start'),
                $request->validated('appointment_end'),
            );
            $holdToken = $request->validated('slot_hold_token');
            $hold = $holdToken ? $this->holds->lockValid(
                $request->user(),
                $holdToken,
                $request->integer('user_id'),
                $request->validated('appointment_start'),
                $request->validated('appointment_end'),
            ) : null;

            User::whereKey($request->integer('user_id'))->lockForUpdate()->firstOrFail();
            $service = Services::findOrFail($request->integer('service_id'));
            $customer = $this->resolveCustomer($request);
            $seriesUuid = $repeatCount > 1 ? (string) Str::uuid() : null;
            $basePayload = $request->safe()->except([
                'slot_hold_token', 'repeat_count', 'repeat_interval_weeks', 'price',
            ]);
            $basePayload['client_lastname'] = (string) ($basePayload['client_lastname'] ?? '');
            $basePayload['client_locale'] = ($basePayload['client_locale'] ?? null) ?: ($customer?->locale ?: app(SiteLocaleRegistry::class)->defaultCode());
            $basePayload['customer_identity_verified'] = false;
            if ($customer) {
                $basePayload['customer_id'] = $customer->id;
                $basePayload['client_name'] = $customer->first_name;
                $basePayload['client_lastname'] = (string) $customer->last_name;
                $basePayload['client_email'] = $customer->email;
                $basePayload['client_phone'] = $customer->phone;
            }

            $firstStartAt = Carbon::parse($firstDate.' '.$firstStart);
            $firstEndAt = Carbon::parse($firstDate.' '.$firstEnd);
            $created = collect();

            for ($sequence = 1; $sequence <= $repeatCount; $sequence++) {
                $weeks = ($sequence - 1) * $repeatInterval;
                $startAt = $firstStartAt->copy()->addWeeks($weeks);
                $endAt = $firstEndAt->copy()->addWeeks($weeks);
                $date = $startAt->toDateString();
                $start = $startAt->format('H:i');
                $end = $endAt->format('H:i');
                $excludedHold = $sequence === 1 ? $holdToken : null;

                try {
                    $this->assertAvailable(
                        $date,
                        $start,
                        $end,
                        $service->id,
                        $request->integer('user_id'),
                        null,
                        $excludedHold,
                    );
                    $roomId = $this->rooms->assign(
                        $request->integer('user_id'),
                        $date,
                        $start,
                        $end,
                        null,
                        null,
                        $excludedHold,
                    );
                    $this->assertRoomAvailable($request->integer('user_id'), $roomId);
                } catch (RuntimeException $exception) {
                    throw new RuntimeException(__('admin_appointment_create.series_conflict', [
                        'date' => $startAt->translatedFormat('d M Y H:i'),
                        'message' => $exception->getMessage(),
                    ]));
                }

                $price = $service->effectivePriceForDate($date);
                $appointment = Appointments::create(array_merge($basePayload, [
                    'appointment_start' => $startAt,
                    'appointment_end' => $endAt,
                    'room_id' => $roomId,
                    'price' => $price,
                    'original_price' => $price,
                    'discount_amount' => 0,
                    'series_uuid' => $seriesUuid,
                    'series_sequence' => $seriesUuid ? $sequence : null,
                    'series_total' => $seriesUuid ? $repeatCount : null,
                ]));
                $this->audit->created($appointment, Auth::user());
                $created->push($appointment);
            }

            $hold?->delete();

            return $created;
        }, 3);

        $appointments->each(fn (Appointments $appointment) => $this->notifications->send($appointment, 'booking_created'));

        return $appointments;
    }

    public function update(AppointmentRequest $request): Appointments
    {
        return DB::transaction(function () use ($request) {
            $appointment = Appointments::whereKey($request->id)->lockForUpdate()->firstOrFail();
            $before = $this->audit->snapshot($appointment);
            User::whereKey($request->user_id)->lockForUpdate()->firstOrFail();
            [$date, $start, $end] = $this->interval($request->appointment_start, $request->appointment_end);
            $this->assertAvailable($date, $start, $end, (int) $request->service_id, (int) $request->user_id, $appointment->id);
            $roomId = $this->rooms->assign((int) $request->user_id, $date, $start, $end, $appointment->id);
            $this->assertRoomAvailable((int) $request->user_id, $roomId);
            $service = Services::findOrFail($request->service_id);

            $pricingChanged = (int) $appointment->service_id !== (int) $request->service_id
                || ! $appointment->appointment_start->isSameDay(Carbon::parse($request->appointment_start));
            $pricing = $pricingChanged ? [
                'promo_code_id' => null,
                'promo_code' => null,
                'original_price' => $service->effectivePriceForDate($date),
                'discount_amount' => 0,
                'price' => $service->effectivePriceForDate($date),
            ] : [];

            $appointment->update(array_merge($request->safe()->except([
                'slot_hold_token', 'repeat_count', 'repeat_interval_weeks',
            ]), ['room_id' => $roomId], $pricing));
            $this->audit->updated($appointment, $before, Auth::user());

            return $appointment->refresh();
        });
    }

    private function interval(string $startValue, string $endValue): array
    {
        $start = Carbon::parse($startValue);
        $end = Carbon::parse($endValue);
        if (! $start->isSameDay($end)) {
            throw new RuntimeException(__('admin_appointment_create.errors.same_day'));
        }

        return [$start->format('Y-m-d'), $start->format('H:i'), $end->format('H:i')];
    }

    private function assertAvailable(string $date, string $start, string $end, int $serviceId, int $userId, ?int $excludedId = null, ?string $excludedHoldToken = null): void
    {
        if (! $this->availability->containsSlot($date, $serviceId, $userId, $start, $end, $excludedId, null, $excludedHoldToken)) {
            throw new RuntimeException(__('admin_appointment_create.errors.unavailable'));
        }
    }

    private function assertRoomAvailable(int $userId, ?int $roomId): void
    {
        if ($roomId === null && User::whereKey($userId)->whereHas('rooms')->exists()) {
            throw new RuntimeException(__('admin_appointment_create.errors.room_unavailable'));
        }
    }

    private function resolveCustomer(AppointmentRequest $request): ?Customer
    {
        if ($request->filled('customer_id')) {
            return Customer::whereKey($request->integer('customer_id'))->lockForUpdate()->firstOrFail();
        }

        if ($request->filled('client_email')) {
            return $this->customers->resolveForBooking(
                $request->string('client_name')->toString(),
                $request->input('client_lastname'),
                $request->string('client_email')->toString(),
                $request->string('client_phone')->toString(),
            );
        }

        $phone = $this->customers->normalizePhone($request->string('client_phone')->toString());

        return Customer::where('phone', $phone)->lockForUpdate()->first();
    }
}
