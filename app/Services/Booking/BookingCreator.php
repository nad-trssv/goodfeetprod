<?php

namespace App\Services\Booking;

use App\Http\Requests\BookingRequest;
use App\Models\AppointmentMedia;
use App\Models\Appointments;
use App\Models\Services;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingCreator
{
    public function __construct(private readonly RoomAllocationService $rooms)
    {
    }

    public function create(BookingRequest $request, array $slot, Services $service): Appointments
    {
        $start = Carbon::createFromFormat('Y-m-d H:i', $request->choose_date.' '.$slot['start']);
        $end = Carbon::createFromFormat('Y-m-d H:i', $request->choose_date.' '.$slot['end']);

        return DB::transaction(function () use ($request, $service, $start, $end, $slot) {
            User::whereKey($request->user_id)->lockForUpdate()->firstOrFail();

            abort_if($this->hasConflict($request->user_id, $start, $end), 409, 'The selected slot is no longer available.');

            $roomId = $this->rooms->assign($request->user_id, $request->choose_date, $slot['start'], $slot['end']);
            abort_if(User::whereKey($request->user_id)->whereHas('rooms')->exists() && $roomId === null, 409, 'No room is available for the selected slot.');

            $appointment = Appointments::create([
                'user_id' => $request->user_id,
                'room_id' => $roomId,
                'service_id' => $service->id,
                'price' => $service->effectivePriceForDate($request->choose_date),
                'appointment_start' => $start,
                'appointment_end' => $end,
                'client_email' => $request->client_email,
                'client_phone' => $request->client_phone,
                'client_name' => $request->client_name,
                'client_lastname' => $request->client_lastname,
                'description' => $request->description,
            ]);

            foreach ($request->file('files', []) as $file) {
                AppointmentMedia::create([
                    'appointment_id' => $appointment->id,
                    'photo_path' => $file->store('appointments', 'public'),
                ]);
            }

            return $appointment;
        });
    }

    private function hasConflict(int $userId, Carbon $start, Carbon $end): bool
    {
        return Appointments::where('user_id', $userId)
            ->where('appointment_start', '<', $end)
            ->where('appointment_end', '>', $start)
            ->exists();
    }
}
