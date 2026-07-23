<?php

namespace App\Services\Api;

use App\Http\Requests\BookingRequest;
use App\Models\Appointments;
use App\Models\RedDay;
use App\Models\Services;
use App\Models\SiteSettings;
use App\Models\UserSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\Booking\BookingCreator;
use App\Services\Booking\BookingCalendarService;
use App\Services\Booking\RoomAllocationService;
use App\Services\Booking\SlotAvailabilityService;

class GetFullyBookedService
{
    public function __construct(
        private readonly RoomAllocationService $rooms,
        private readonly BookingCreator $creator,
        private readonly BookingCalendarService $calendar,
        private readonly SlotAvailabilityService $availability,
    ) {
    }

    public function getList($request)
    {
        try {
        $check = $this->calendar->isClosed($request->choose_date, $request->user_id);
            if ($check === true) {
                return [
                    'status' => 'error',
                    'slots' => [],
                    'error' => 'Broneerimine selleks päevaks ei ole võimalik!'
                ];
            }
            return [
                'slots' => $this->availability->slots($request->choose_date, (int) $request->service_id, (int) $request->user_id),
            ];

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw $exception;
        }
    }

    public function store(BookingRequest $request)
    {
        $service = Services::where('status', 1)->where('is_deleted', 0)->findOrFail($request->service_id);
        abort_unless($service->users()->whereKey($request->user_id)->exists(), 422);
        $slot = collect($this->getList($request)['slots'])->first(
            fn ($slot) => $slot['start'] === $request->appointment_start && $slot['end'] === $request->appointment_end
        );
        abort_unless($slot, 409, 'The selected slot is no longer available.');
        $appointment = $this->creator->create($request, $slot, $service);

        return response()->json([
            'message' => 'Booking created successfully.',
            'appointment' => $appointment,
            'appointmentId' => $appointment->id,
        ], 201);
    }

function getBusyDays($request)
{
    $userId = $request->user_id;
    $limit = $this->calendar->bookingLimit();
    $days = (int) $limit['days'];

    $today = Carbon::now()->format('Y-m-d');
    $limitDay = Carbon::now()->addDays($days)->format('Y-m-d');
    $rangeStart = $request->input('range_start', $today);
    $rangeEnd = $request->input('range_end', $limitDay);
    $start = Carbon::parse($rangeStart)->max(Carbon::parse($today));
    $end = Carbon::parse($rangeEnd)->min(Carbon::parse($limitDay));

    // A calendar view contains at most six weeks. Clamp arbitrary public
    // requests so this endpoint can never trigger an unbounded day-by-day scan.
    if ($start->diffInDays($end, false) > 62) {
        $end = $start->copy()->addDays(62);
    }

    if ($end->lt($start)) {
        return [];
    }

    $busyDays = [];
    $service = Services::where('status', 1)->where('is_deleted', 0)->findOrFail($request->service_id);

    // Кэшируем данные мастера
    $userSchedule = UserSchedule::where('user_id', $userId)->first();
        $fixedBooking = SiteSettings::where('group', 'hours')->where('key', 'fixed_booking_hours')->value('payload');
        $fixedData = json_decode($fixedBooking, true);
        $hasFixed = ($userSchedule && $userSchedule->fixed_booking_enabled && !empty($userSchedule->fixed_booking_slots))
            || (isset($fixedData['value']) && $fixedData['value'] == 1);

    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
        $day = $date->format('Y-m-d');

        // Проверяем полностью закрытый день
        if ($this->calendar->isClosed($day, $userId)) {
            $busyDays[] = $day;
            continue;
        }

        $workingHours = $this->calendar->workHours($day, $userId);
        if (!$workingHours || !$workingHours['start'] || !$workingHours['end']) {
            $busyDays[] = $day;
            continue;
        }
        $availableSlots = $service->has_fixed_time === 1
            ? $this->getAvailableSlotsForFixed($day, $request->service_id, $userId)
            : $this->getAvailableSlots($day, $request->service_id, $userId);

        $duration = $this->calendar->serviceDuration($request->service_id, $day);
        $hasEnoughSpace = false;

        foreach ($availableSlots as $slot) {
            $slotStart = strtotime($slot['start']);
            $slotEnd = strtotime($slot['end']);
            if (($slotEnd - $slotStart) >= ($duration * 60)) {
                $hasEnoughSpace = true;
                break;
            }
        }

        if (!$hasEnoughSpace) {
            $busyDays[] = $day;
        } elseif ($day === $today) {
            // Если сегодня — проверяем есть ли будущие слоты
            $futureSlots = array_filter($availableSlots, function($slot) {
                return Carbon::parse($slot['start'])->gt(Carbon::now());
            });
            if (empty($futureSlots)) {
                $busyDays[] = $day;
            }
        }
    }
    return $busyDays;
}

    /**
     * Рабочие часы мастера — сначала из user_schedules, потом из site_settings
     */
    function getWorkHours($date, $userId = null)
    {
        $dayOfWeek = strtolower(Carbon::parse($date)->format('l'));

        // Пробуем взять из персонального расписания мастера
        if ($userId && $userId !== 'all') {
            $schedule = UserSchedule::where('user_id', $userId)->first();
            Log::info('getWorkHours userId='.$userId.' day='.$dayOfWeek.' schedule='.json_encode($schedule));
            if ($schedule) {
                $start = $schedule->{$dayOfWeek . '_start'};
                $end = $schedule->{$dayOfWeek . '_end'};
                if ($start && $end) {
                    return ['start' => $start, 'end' => $end];
                }
                // Если день не заполнен — выходной
                return ['start' => null, 'end' => null];
            }
        }

        // Fallback — глобальные настройки
        $siteSettings = SiteSettings::where('group', 'hours')
            ->where('key', 'work_hours')
            ->first();

        if ($siteSettings) {
            $workHours = json_decode($siteSettings->payload, true);
            if (isset($workHours[$dayOfWeek])) {
                return $workHours[$dayOfWeek];
            }
        }

        return null;
    }

    /**
     * Обед мастера — сначала из user_schedules, потом из site_settings
     */
    function getLunchHours($userId = null)
    {
        if ($userId && $userId !== 'all') {
            $schedule = UserSchedule::where('user_id', $userId)->first();
            if ($schedule && $schedule->lunch_start && $schedule->lunch_end) {
                return [
                    'start' => $schedule->lunch_start,
                    'end' => $schedule->lunch_end,
                ];
            }
        }

        // Fallback — глобальные настройки
        $siteSettings = SiteSettings::where('group', 'hours')
            ->where('key', 'lunch_hours')
            ->first();

        if ($siteSettings) {
            return json_decode($siteSettings->payload, true);
        }

        return ['start' => null, 'end' => null];
    }

    /**
     * Лимит дней бронирования — сначала персональный, потом глобальный
     */
    function getBookingLimit($userId = null)
    {
        // Пока лимит глобальный — в будущем можно добавить в user_schedules
        $siteSettings = SiteSettings::where('group', 'hours')
            ->where('key', 'booking_date_limit')
            ->first();

        if ($siteSettings) {
            return json_decode($siteSettings->payload, true);
        }

        return ['days' => 30, 'active' => false];
    }

    function getRedDays($date, $userId = null) {
        $query = RedDay::where('full_day', 0)->where('date', $date);

        if ($userId && $userId !== 'all') {
            $query->visibleFor($userId);
        } else {
            $query->whereNull('user_id');
        }

        $reddays = $query->get();

        if ($reddays && $reddays instanceof \Illuminate\Database\Eloquent\Collection) {
            return $reddays->toArray();
        }

        return [];
    }

    function getServiceDuration($serviceId, $date)
    {
        $service = Services::with('rules')->find($serviceId);
        if (!$service) return null;

        $rule = $service->ruleForDate($date);
        if ($rule && $rule->duration_minutes) {
            return (int) $rule->duration_minutes;
        }

        return (int) $service->duration_minutes;
    }

    function getBookedEvents($userId, $date)
    {
        if ($userId !== 'all') {
            $appointments = Appointments::where('user_id', $userId)->whereDate('appointment_start', $date)->get();
        } else {
            $appointments = Appointments::whereDate('appointment_start', $date)->get();
        }

        $events = [];
        foreach ($appointments as $appint) {
            $events[] = [
                'id' => $appint->id,
                'title' => $appint->service->name,
                'user_id' => $appint->user_id,
                'client_phone' => $appint->client_phone,
                'client_name' => $appint->client_name,
                'client_lastname' => $appint->client_lastname,
                'service_id' => $appint->service_id,
                'textColor' => $appint->service->eventColor,
                'description' => $appint->description,
                'price' => $appint->price,
                'start' => $appint->appointment_start,
                'end' => $appint->appointment_end,
            ];
        }
        return $events;
    }

function isDayFree($userId, $serviceId, $date){
    $query = RedDay::where('full_day', 1)->where(function($q) use ($date) {
        $q->where('date', $date)
          ->orWhere(function($q2) use ($date) {
              $q2->where('repeat', 1)
                 ->whereMonth('date', \Carbon\Carbon::parse($date)->month)
                 ->whereDay('date', \Carbon\Carbon::parse($date)->day);
          });
    });

    if ($userId && $userId !== 'all') {
        $query->visibleFor($userId);
    } else {
        $query->whereNull('user_id');
    }

    $result = $query->get();

    if ($result->isNotEmpty()) {
        return true;
    }
    return false;
}

    function getAvailableSlots($date, $serviceId, $userId)
    {
        return $this->availability->slots($date, (int) $serviceId, (int) $userId);

        $workHours = $this->calendar->workHours($date, $userId);

        // Нет рабочих часов — нет слотов
        if (!$workHours || !$workHours['start'] || !$workHours['end']) {
            return [];
        }

        $lunchHours = $this->calendar->lunchHours($userId);
        $redDays = $this->calendar->partialClosures($date, $userId);
        $bookedEvents = $this->calendar->bookedEvents($userId, $date);
        $serviceDuration = $this->calendar->serviceDuration($serviceId, $date);

        // Проверяем персональное фиксированное время мастера
        $userSchedule = $userId && $userId !== 'all' ? UserSchedule::where('user_id', $userId)->first() : null;

        if ($userSchedule && $userSchedule->fixed_booking_enabled && !empty($userSchedule->fixed_booking_slots)) {
            $isFixedBooking = true;
            $fixedSlots = $userSchedule->fixed_booking_slots;
        } else {
            // Fallback на глобальные настройки
            $fixedBooking = SiteSettings::where('group', 'hours')->where('key', 'fixed_booking_hours')->value('payload');
            $dataArray = json_decode($fixedBooking, true);
            $isFixedBooking = isset($dataArray['value']) && $dataArray['value'] == 1;
            $fixedSlots = $isFixedBooking ? $dataArray['payload'] : [];
        }

        $availableSlots = [];
        $now = Carbon::now();
        $isToday = Carbon::parse($date)->isToday();

        $startTime = Carbon::parse($workHours['start']);
        $endTime = Carbon::parse($workHours['end']);

        // Обед
        $lunchStart = ($lunchHours && !empty($lunchHours['start'])) ? Carbon::parse($lunchHours['start']) : null;
        $lunchEnd = ($lunchHours && !empty($lunchHours['end'])) ? Carbon::parse($lunchHours['end']) : null;

        // Красные дни (частичные)
        $redDayArray = [];
        foreach ($redDays as $redDay) {
            $redDayArray[] = [
                'start_time' => Carbon::parse($redDay['start_time']),
                'end_time' => Carbon::parse($redDay['end_time']),
            ];
        }

        if ($isFixedBooking) {
            foreach ($fixedSlots as $fixedTime) {
                $slotStart = Carbon::parse($fixedTime);
                $slotEnd = $slotStart->copy()->addMinutes($serviceDuration);

                // Проверка обеда
                if ($lunchStart && $lunchEnd) {
                    if ($slotStart->lt($lunchEnd) && $slotEnd->gt($lunchStart)) {
                        continue;
                    }
                }

                $isAvailable = true;
                foreach ($bookedEvents as $event) {
                    $eventStart = Carbon::parse($event['start'])->format('H:i');
                    $eventEnd = Carbon::parse($event['end'])->format('H:i');
                    if ($slotStart->format('H:i') < $eventEnd && $slotEnd->format('H:i') > $eventStart) {
                        $isAvailable = false;
                        break;
                    }
                }

                if ($isAvailable) {
                    if ($this->rooms->hasCapacity($userId, $date, $slotStart->format('H:i'), $slotEnd->format('H:i'))) {
                        $availableSlots[] = [
                            'start' => $slotStart->format('H:i'),
                            'end' => $slotEnd->format('H:i'),
                        ];
                    }
                }
            }
            return $availableSlots;
        }

        usort($bookedEvents, function ($a, $b) {
            return Carbon::parse($a['start'])->timestamp - Carbon::parse($b['start'])->timestamp;
        });

        for ($time = $startTime->copy(); $time->lt($endTime); $time->addMinutes(30)) {
            $slotStart = $time->copy();
            $slotEnd = $slotStart->copy()->addMinutes($serviceDuration);

            // Не показывать прошедшие слоты сегодня
            if ($isToday && $slotStart->lt($now)) {
                continue;
            }

            // Слот не выходит за рабочее время
            if ($slotEnd->gt($endTime)) {
                continue;
            }

            // Проверка обеда — слот не должен пересекаться с обедом
            if ($lunchStart && $lunchEnd) {
                if ($slotStart->lt($lunchEnd) && $slotEnd->gt($lunchStart)) {
                    continue;
                }
            }

            // Проверка красных дней (частичных)
            foreach ($redDayArray as $redDay) {
                if ($slotStart->lt($redDay['end_time']) && $slotEnd->gt($redDay['start_time'])) {
                    continue 2;
                }
            }

            // Проверка занятых записей
            $isAvailable = true;
            foreach ($bookedEvents as $event) {
                $eventStart = Carbon::parse($event['start'])->format('H:i');
                $eventEnd = Carbon::parse($event['end'])->format('H:i');
                $slotStartTime = $slotStart->format('H:i');
                $slotEndTime = $slotEnd->format('H:i');

                if ($slotStartTime < $eventEnd && $slotEndTime > $eventStart) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                if ($this->rooms->hasCapacity($userId, $date, $slotStart->format('H:i'), $slotEnd->format('H:i'))) {
                    $availableSlots[] = [
                        'start' => $slotStart->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                    ];
                }
            }
        }

        return $availableSlots;
    }

    function getAvailableSlotsForFixed($date, $serviceId, $userId)
    {
        return $this->availability->slots($date, (int) $serviceId, (int) $userId);

        $fixedTimeCheck = Services::where('id', $serviceId)->first(['time_from', 'time_to']);

        $workHours = [
            'start' => $fixedTimeCheck->time_from,
            'end' => $fixedTimeCheck->time_to,
        ];

        $lunchHours = $this->calendar->lunchHours($userId);
        $redDays = $this->calendar->partialClosures($date, $userId);
        $bookedEvents = $this->calendar->bookedEvents($userId, $date);
        $serviceDuration = $this->calendar->serviceDuration($serviceId, $date);
        $availableSlots = [];

        $startTime = strtotime($workHours['start']);
        $endTime = strtotime($workHours['end']);

        // Обед
        $lunchStart = ($lunchHours && !empty($lunchHours['start'])) ? strtotime($lunchHours['start']) : null;
        $lunchEnd = ($lunchHours && !empty($lunchHours['end'])) ? strtotime($lunchHours['end']) : null;

        usort($bookedEvents, function ($a, $b) {
            return strtotime($a['start']) - strtotime($b['start']);
        });

        for ($time = $startTime; $time < $endTime; $time += 1800) {
            $slotStart = date('H:i', $time);
            $slotEnd = date('H:i', $time + ($serviceDuration * 60));

            if (strtotime($slotEnd) > $endTime) continue;

            // Проверка обеда
            if ($lunchStart && $lunchEnd) {
                if ($time < $lunchEnd && strtotime($slotEnd) > $lunchStart) continue;
            }

            $isAvailable = true;
            $slotStartDateTime = strtotime($date . ' ' . $slotStart);
            $slotEndDateTime = strtotime($date . ' ' . $slotEnd);

            foreach ($bookedEvents as $event) {
                $eventStartDateTime = strtotime($event['start']);
                $eventEndDateTime = strtotime($event['end']);

                if ($slotStartDateTime < $eventEndDateTime && $slotEndDateTime > $eventStartDateTime) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                if ($this->rooms->hasCapacity($userId, $date, $slotStart, $slotEnd)) {
                    $availableSlots[] = [
                        'start' => $slotStart,
                        'end' => $slotEnd,
                    ];
                }
            }
        }

        return $availableSlots;
    }
    
    function getRoomCapacityForSlot($userId, $date, $slotStart, $slotEnd)
    {
        $user = \App\Models\User::with('rooms.users')->find($userId);
        if (!$user || $user->rooms->isEmpty()) {
            return true;
        }

        $startDateTime = $date . ' ' . $slotStart . ':00';
        $endDateTime = $date . ' ' . $slotEnd . ':00';

        foreach ($user->rooms as $room) {
            if (!$room->is_active) continue;
            if ($this->canRoomAccommodate($room, $startDateTime, $endDateTime, $userId, [])) {
                return true;
            }
        }

        return false;
    }

    function canRoomAccommodate($room, $startDateTime, $endDateTime, $excludedMasterId, $visited)
    {
        $key = $room->id . '_' . $excludedMasterId;
        
        if (in_array($key, $visited)) {
            return false;
        }
        $visited[] = $key;

        $mustStayCount = 0;

        foreach ($room->users as $master) {
            if ($master->id == $excludedMasterId) continue;

            $hasBooking = \App\Models\Appointments::where('user_id', $master->id)
                ->where('appointment_start', '<', $endDateTime)
                ->where('appointment_end', '>', $startDateTime)
                ->exists();

            if (!$hasBooking) continue;

            // Может ли этот мастер переместиться в другой кабинет?
            $masterWithRooms = \App\Models\User::with('rooms.users')->find($master->id);
            $canMove = false;

            foreach ($masterWithRooms->rooms as $otherRoom) {
                if ($otherRoom->id == $room->id || !$otherRoom->is_active) continue;
                if ($this->canRoomAccommodate($otherRoom, $startDateTime, $endDateTime, $master->id, $visited)) {
                    $canMove = true;
                    break;
                }
            }

            if (!$canMove) {
                $mustStayCount++;
            }
        }

        return $mustStayCount < $room->capacity;
    }
    
    function assignRoom($userId, $date, $slotStart, $slotEnd)
    {
        $user = \App\Models\User::with('rooms.users')->find($userId);
        if (!$user || $user->rooms->isEmpty()) {
            return null;
        }

        $startDateTime = $date . ' ' . $slotStart . ':00';
        $endDateTime = $date . ' ' . $slotEnd . ':00';

        foreach ($user->rooms as $room) {
            if (!$room->is_active) continue;

            if ($this->canRoomAccommodate($room, $startDateTime, $endDateTime, $userId, [])) {
                // Переназначаем мастеров у которых нет room_id или которых нужно сдвинуть
                $this->reassignMastersInRoom($room, $startDateTime, $endDateTime, $userId);
                return $room->id;
            }
        }

        return null;
    }

    function reassignMastersInRoom($room, $startDateTime, $endDateTime, $excludedMasterId)
    {
        foreach ($room->users as $master) {
            if ($master->id == $excludedMasterId) continue;

            $appointments = \App\Models\Appointments::where('user_id', $master->id)
                ->where('appointment_start', '<', $endDateTime)
                ->where('appointment_end', '>', $startDateTime)
                ->where(function($q) use ($room) {
                    $q->where('room_id', $room->id)
                    ->orWhereNull('room_id');
                })
                ->get();

            foreach ($appointments as $appointment) {
                $masterWithRooms = \App\Models\User::with('rooms.users')->find($master->id);
                foreach ($masterWithRooms->rooms as $otherRoom) {
                    if ($otherRoom->id == $room->id || !$otherRoom->is_active) continue;
                    if ($this->canRoomAccommodate($otherRoom, $startDateTime, $endDateTime, $master->id, [])) {
                        $appointment->update(['room_id' => $otherRoom->id]);
                        break;
                    }
                }
            }
        }
    }
}
