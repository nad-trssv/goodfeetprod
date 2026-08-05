<?php

namespace App\Services\Booking;

use App\Models\AppointmentRescheduleRequest;
use App\Models\Appointments;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentRescheduleService
{
    public function __construct(
        private readonly SlotAvailabilityService $availability,
        private readonly RoomAllocationService $rooms,
        private readonly CustomerCancellationService $deadlines,
        private readonly AppointmentNotificationService $notifications,
        private readonly AppointmentAuditService $audit,
    ) {}

    public function request(Appointments $appointment, Customer $customer, string $date, string $start, string $end, ?string $reason): AppointmentRescheduleRequest
    {
        if ($appointment->customer_id !== $customer->id) abort(404);
        if (! in_array($appointment->status, ['pending', 'confirmed'], true)) throw ValidationException::withMessages(['appointment' => __('customer.reschedule_not_available')]);
        if (now()->gte($appointment->appointment_start->copy()->subHours($this->deadlines->noticeHours()))) throw ValidationException::withMessages(['appointment' => __('customer.reschedule_too_late', ['hours' => $this->deadlines->noticeHours()])]);

        $requestedStart = Carbon::createFromFormat('Y-m-d H:i', "$date $start");
        $requestedEnd = Carbon::createFromFormat('Y-m-d H:i', "$date $end");
        if ($requestedStart->equalTo($appointment->appointment_start) && $requestedEnd->equalTo($appointment->appointment_end)) throw ValidationException::withMessages(['appointment' => __('customer.choose_different_time')]);

        $rescheduleRequest = DB::transaction(function () use ($appointment, $customer, $date, $start, $end, $reason, $requestedStart, $requestedEnd) {
            User::whereKey($appointment->user_id)->lockForUpdate()->firstOrFail();
            $appointment = Appointments::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            if ($appointment->rescheduleRequests()->where('status', 'pending')->exists()) throw ValidationException::withMessages(['appointment' => __('customer.reschedule_already_pending')]);
            if (! $this->availability->containsSlot($date, $appointment->service_id, $appointment->user_id, $start, $end, $appointment->id)) throw ValidationException::withMessages(['appointment' => __('customer.slot_unavailable')]);
            $roomId = $this->rooms->assign($appointment->user_id, $date, $start, $end, $appointment->id);
            if (User::whereKey($appointment->user_id)->whereHas('rooms')->exists() && $roomId === null) throw ValidationException::withMessages(['appointment' => __('customer.slot_unavailable')]);

            $rescheduleRequest = AppointmentRescheduleRequest::create([
                'appointment_id'=>$appointment->id,'customer_id'=>$customer->id,'user_id'=>$appointment->user_id,'service_id'=>$appointment->service_id,'room_id'=>$roomId,
                'old_start'=>$appointment->appointment_start,'old_end'=>$appointment->appointment_end,'requested_start'=>$requestedStart,'requested_end'=>$requestedEnd,'status'=>'pending','reason'=>$reason,
            ]);
            $this->audit->event($appointment, 'reschedule_requested', $customer, $reason, [
                'appointment_start' => ['old' => $appointment->appointment_start->format('Y-m-d H:i:s'), 'new' => $requestedStart->format('Y-m-d H:i:s')],
                'appointment_end' => ['old' => $appointment->appointment_end->format('Y-m-d H:i:s'), 'new' => $requestedEnd->format('Y-m-d H:i:s')],
            ], ['reschedule_request_uuid' => $rescheduleRequest->public_uuid]);

            return $rescheduleRequest;
        });

        $this->notifications->send($appointment->refresh(), 'reschedule_requested', $rescheduleRequest);

        return $rescheduleRequest;
    }

    public function review(AppointmentRescheduleRequest $request, User $reviewer, bool $approve): AppointmentRescheduleRequest
    {
        if ($reviewer->role_id !== 1 && $reviewer->id !== $request->user_id) abort(403);
        return DB::transaction(function () use ($request, $reviewer, $approve) {
            User::whereKey($request->user_id)->lockForUpdate()->firstOrFail();
            $request = AppointmentRescheduleRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();
            if ($request->status !== 'pending') throw ValidationException::withMessages(['request' => __('customer.reschedule_reviewed')]);
            $appointment = Appointments::whereKey($request->appointment_id)->lockForUpdate()->firstOrFail();
            $before = $this->audit->snapshot($appointment);
            if ($approve) {
                $date = $request->requested_start->toDateString(); $start = $request->requested_start->format('H:i'); $end = $request->requested_end->format('H:i');
                if (! $this->availability->containsSlot($date, $request->service_id, $request->user_id, $start, $end, $appointment->id, $request->id)) throw ValidationException::withMessages(['request' => __('customer.slot_unavailable')]);
                $roomId = $this->rooms->assign($request->user_id, $date, $start, $end, $appointment->id, $request->id);
                if (User::whereKey($request->user_id)->whereHas('rooms')->exists() && $roomId === null) throw ValidationException::withMessages(['request' => __('customer.slot_unavailable')]);
                $appointment->update(['appointment_start'=>$request->requested_start,'appointment_end'=>$request->requested_end,'room_id'=>$roomId]);
            }
            $request->update(['status'=>$approve ? 'approved' : 'rejected','reviewed_by'=>$reviewer->id,'reviewed_at'=>now()]);
            if ($approve) {
                $this->audit->updated($appointment, $before, $reviewer, $request->reason, 'reschedule_approved', ['reschedule_request_uuid' => $request->public_uuid]);
            } else {
                $this->audit->event($appointment, 'reschedule_rejected', $reviewer, $request->reason, [], ['reschedule_request_uuid' => $request->public_uuid]);
            }
            return $request->refresh();
        });
    }
}
