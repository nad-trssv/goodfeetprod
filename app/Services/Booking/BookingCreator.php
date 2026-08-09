<?php

namespace App\Services\Booking;

use App\Http\Requests\BookingRequest;
use App\Models\AppointmentMedia;
use App\Models\Appointments;
use App\Models\Services;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\Customer\CustomerIdentityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BookingCreator
{
    public function __construct(
        private readonly RoomAllocationService $rooms,
        private readonly SlotAvailabilityService $availability,
        private readonly CustomerIdentityService $customers,
        private readonly ?AppointmentAuditService $audit = null,
        private readonly ?AppointmentNotificationService $notifications = null,
        private readonly ?PromoCodePricingService $promoPricing = null,
    ) {
    }

    public function create(BookingRequest $request, array $slot, Services $service): Appointments
    {
        $start = Carbon::createFromFormat('Y-m-d H:i', $request->choose_date.' '.$slot['start']);
        $end = Carbon::createFromFormat('Y-m-d H:i', $request->choose_date.' '.$slot['end']);

        $appointment = DB::transaction(function () use ($request, $service, $start, $end, $slot) {
            User::whereKey($request->user_id)->lockForUpdate()->firstOrFail();

            abort_unless($this->availability->containsSlot(
                $request->choose_date,
                $service->id,
                (int) $request->user_id,
                $slot['start'],
                $slot['end'],
            ), 409, 'The selected slot is no longer available.');

            $roomId = $this->rooms->assign($request->user_id, $request->choose_date, $slot['start'], $slot['end']);
            abort_if(User::whereKey($request->user_id)->whereHas('rooms')->exists() && $roomId === null, 409, 'No room is available for the selected slot.');
            $customer = Auth::guard('customer')->user();
            $identityVerified = $customer !== null;
            if ($customer) {
                if ($customer->email !== $this->customers->normalizeEmail($request->client_email)
                    || $customer->phone !== $this->customers->normalizePhone($request->client_phone)) {
                    throw ValidationException::withMessages([
                        'client_email' => 'Для записи из аккаунта используйте контактные данные профиля.',
                    ]);
                }
            } else {
                $customer = $this->customers->resolveForBooking(
                    $request->client_name,
                    $request->client_lastname,
                    $request->client_email,
                    $request->client_phone,
                );
            }

            $pricing = ($this->promoPricing ?? app(PromoCodePricingService::class))->quote(
                $service,
                $request->choose_date,
                $request->input('promo_code'),
                $customer,
                $request->client_email,
                true,
            );

            $appointment = Appointments::create([
                'customer_id' => $customer->id,
                'customer_identity_verified' => $identityVerified,
                'status' => 'confirmed',
                'user_id' => $request->user_id,
                'room_id' => $roomId,
                'service_id' => $service->id,
                'promo_code_id' => $pricing['promo']?->id,
                'promo_code' => $pricing['promo']?->code,
                'original_price' => $pricing['original_price'],
                'discount_amount' => $pricing['discount_amount'],
                'price' => $pricing['final_price'],
                'appointment_start' => $start,
                'appointment_end' => $end,
                'client_email' => $request->client_email,
                'client_phone' => $request->client_phone,
                'client_name' => $request->client_name,
                'client_lastname' => $request->client_lastname,
                'description' => $request->description,
            ]);
            ($this->audit ?? app(AppointmentAuditService::class))->created($appointment, $customer);

            if ($pricing['promo']) {
                ($this->promoPricing ?? app(PromoCodePricingService::class))->redeem(
                    $pricing['promo'], $appointment, $customer, $request->client_email,
                    $pricing['original_price'], $pricing['discount_amount'],
                );
            }

            foreach ($request->file('files', []) as $file) {
                AppointmentMedia::create([
                    'appointment_id' => $appointment->id,
                    'photo_path' => $file->store('appointments', 'public'),
                ]);
            }

            return $appointment;
        });

        ($this->notifications ?? app(AppointmentNotificationService::class))->send($appointment, 'booking_created');

        return $appointment;
    }

}
