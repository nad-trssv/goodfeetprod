<?php

namespace App\Services\Booking;

use App\Http\Requests\AppointmentRequest;
use App\Models\Appointments;
use App\Models\Services;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AppointmentScheduler
{
    public function __construct(
        private readonly SlotAvailabilityService $availability,
        private readonly RoomAllocationService $rooms,
        private readonly AppointmentNotificationService $notifications,
    ) {
    }

    public function create(AppointmentRequest $request): Appointments
    {
        $appointment = DB::transaction(function () use ($request) {
            User::whereKey($request->user_id)->lockForUpdate()->firstOrFail();
            [$date, $start, $end] = $this->interval($request);
            $this->assertAvailable($date, $start, $end, (int) $request->service_id, (int) $request->user_id);
            $roomId = $this->rooms->assign((int) $request->user_id, $date, $start, $end);
            $this->assertRoomAvailable((int) $request->user_id, $roomId);
            $service = Services::findOrFail($request->service_id);

            return Appointments::create(array_merge($request->validated(), [
                'room_id' => $roomId,
                'price' => $service->effectivePriceForDate($date),
            ]));
        });

        $this->notifications->send($appointment, 'booking_created');

        return $appointment;
    }

    public function update(AppointmentRequest $request): Appointments
    {
        return DB::transaction(function () use ($request) {
            $appointment = Appointments::whereKey($request->id)->lockForUpdate()->firstOrFail();
            User::whereKey($request->user_id)->lockForUpdate()->firstOrFail();
            [$date, $start, $end] = $this->interval($request);
            $this->assertAvailable($date, $start, $end, (int) $request->service_id, (int) $request->user_id, $appointment->id);
            $roomId = $this->rooms->assign((int) $request->user_id, $date, $start, $end, $appointment->id);
            $this->assertRoomAvailable((int) $request->user_id, $roomId);
            $service = Services::findOrFail($request->service_id);

            $appointment->update(array_merge($request->validated(), [
                'room_id' => $roomId,
                'price' => $service->effectivePriceForDate($date),
            ]));

            return $appointment->refresh();
        });
    }

    private function interval(AppointmentRequest $request): array
    {
        $start = Carbon::parse($request->appointment_start);
        $end = Carbon::parse($request->appointment_end);
        if (! $start->isSameDay($end)) {
            throw new RuntimeException('Запись должна начинаться и заканчиваться в один день.');
        }

        return [$start->format('Y-m-d'), $start->format('H:i'), $end->format('H:i')];
    }

    private function assertAvailable(string $date, string $start, string $end, int $serviceId, int $userId, ?int $excludedId = null): void
    {
        if (! $this->availability->containsSlot($date, $serviceId, $userId, $start, $end, $excludedId)) {
            throw new RuntimeException('Выбранное время недоступно: проверьте расписание, перерыв, закрытые интервалы и существующие записи.');
        }
    }

    private function assertRoomAvailable(int $userId, ?int $roomId): void
    {
        if ($roomId === null && User::whereKey($userId)->whereHas('rooms')->exists()) {
            throw new RuntimeException('На это время нет свободного кабинета. Выберите другое время.');
        }
    }
}
