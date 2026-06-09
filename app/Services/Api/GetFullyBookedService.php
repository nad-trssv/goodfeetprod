<?php

namespace App\Services\Api;

use App\Http\Requests\BookingRequest;
use App\Models\AppointmentMedia;
use App\Models\Appointments;
use App\Models\RedDay;
use App\Models\Services;
use App\Models\SiteSettings;
use App\Models\UserSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class GetFullyBookedService
{
    public $appointments;

    public function getList($request)
    {
        try {
            $check = $this->isDayFree($request->user_id, $request->service_id, $request->choose_date);
            if ($check === true) {
                return [
                    'status' => 'error',
                    'slots' => [],
                    'error' => 'Broneerimine selleks päevaks ei ole võimalik!'
                ];
            }
            $fixedTimeCheck = Services::where('id', $request->service_id)->first(['has_fixed_time', 'time_from', 'time_to']);

            if ($fixedTimeCheck->has_fixed_time === 1) {
                $availableSlots = $this->getAvailableSlotsForFixed($request->choose_date, $request->service_id, $request->user_id);
            } else {
                $availableSlots = $this->getAvailableSlots($request->choose_date, $request->service_id, $request->user_id);
            }

            return [
                'slots' => $availableSlots,
            ];

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function store(BookingRequest $request)
    {
        $date = $request->choose_date;
        $startTime = $request->appointment_start;
        $endTime = $request->appointment_end;

        $startDateTime = Carbon::createFromFormat('Y-m-d H:i', "$date $startTime");
        $endDateTime = Carbon::createFromFormat('Y-m-d H:i', "$date $endTime");

        $existingAppointment = Appointments::where('user_id', $request->user_id)->where(function ($query) use ($startDateTime, $endDateTime) {
            $query->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where('appointment_start', '<', $endDateTime)
                      ->where('appointment_end', '>', $startDateTime);
            });
        })->exists();

        if ($existingAppointment) {
            return response()->json([
                'error' => 'В это время уже существует бронирование.'
            ], 400);
        }

        try {
            return DB::transaction(function () use ($request, $startDateTime, $endDateTime) {
                $appointment = Appointments::create([
                    'user_id' => $request->user_id,
                    'service_id' => $request->service_id,
                    'price' => $request->price,
                    'appointment_start' => $startDateTime,
                    'appointment_end' => $endDateTime,
                    'client_email' => $request->client_email,
                    'client_phone' => $request->client_phone,
                    'client_name' => $request->client_name,
                    'client_lastname' => $request->client_lastname,
                    'description' => $request->description,
                ]);

                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $file) {
                        $path = $file->store('appointments', 'public');
                        AppointmentMedia::create([
                            'appointment_id' => $appointment->id,
                            'photo_path' => $path,
                        ]);
                    }
                }

                return response()->json([
                    'message' => 'Бронирование успешно создано!',
                    'appointment' => $appointment,
                    'appointmentId' => $appointment['id'],
                ], 201);
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    function getBusyDays($request)
    {
        $userId = $request->user_id;
        $limit = $this->getBookingLimit($userId);
        $days = (int) $limit['days'];

        $today = Carbon::now()->format('Y-m-d');
        $limitDay = Carbon::now()->addDays($days)->format('Y-m-d');

        $busyDays = [];

        for ($date = Carbon::parse($today); $date->lte(Carbon::parse($limitDay)); $date->addDay()) {
            $day = $date->format('Y-m-d');
            $workingHours = $this->getWorkHours($day, $userId);

            if ($workingHours && $workingHours['start'] !== null && $workingHours['end'] !== null) {
                $availableSlots = $this->getAvailableSlots($day, $request->service_id, $userId);
                $duration = $this->getServiceDuration($request->service_id, $day);

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
                }
            } else {
                // Нет рабочих часов — день занят
                $busyDays[] = $day;
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
        $query = RedDay::where('full_day', 1)->where('date', $date);

        if ($userId && $userId !== 'all') {
            $query->visibleFor($userId);
        } else {
            $query->whereNull('user_id');
        }

        if ($query->get()->isNotEmpty()) {
            return true;
        }
        return false;
    }

    function getAvailableSlots($date, $serviceId, $userId)
    {
        $workHours = $this->getWorkHours($date, $userId);

        // Нет рабочих часов — нет слотов
        if (!$workHours || !$workHours['start'] || !$workHours['end']) {
            return [];
        }

        $lunchHours = $this->getLunchHours($userId);
        $redDays = $this->getRedDays($date, $userId);
        $bookedEvents = $this->getBookedEvents($userId, $date);
        $serviceDuration = $this->getServiceDuration($serviceId, $date);

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
                    $availableSlots[] = [
                        'start' => $slotStart->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                    ];
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
                if ($this->getRoomCapacityForSlot($userId, $date, $slotStart->format('H:i'), $slotEnd->format('H:i'))) {
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
        $fixedTimeCheck = Services::where('id', $serviceId)->first(['time_from', 'time_to']);

        $workHours = [
            'start' => $fixedTimeCheck->time_from,
            'end' => $fixedTimeCheck->time_to,
        ];

        $lunchHours = $this->getLunchHours($userId);
        $redDays = $this->getRedDays($date, $userId);
        $bookedEvents = $this->getBookedEvents($userId, $date);
        $serviceDuration = $this->getServiceDuration($serviceId, $date);
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
                if ($this->getRoomCapacityForSlot($userId, $date, $slotStart, $slotEnd)) {
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
        // Получаем кабинеты мастера
        $user = \App\Models\User::with('rooms')->find($userId);
        if (!$user || $user->rooms->isEmpty()) {
            return true; // Нет кабинета — не проверяем
        }

        foreach ($user->rooms as $room) {
            if (!$room->is_active) continue;

            // Считаем сколько записей уже есть в этом кабинете в это время
            $startDateTime = $date . ' ' . $slotStart;
            $endDateTime = $date . ' ' . $slotEnd;

            $busyCount = \App\Models\Appointments::whereHas('user.rooms', function($q) use ($room) {
                $q->where('rooms.id', $room->id);
            })
            ->where(function($q) use ($startDateTime, $endDateTime) {
                $q->where('appointment_start', '<', $endDateTime)
                ->where('appointment_end', '>', $startDateTime);
            })
            ->count();

            if ($busyCount < $room->capacity) {
                return true; // Есть свободное место
            }
        }

        return false; // Все кабинеты заняты
    }
}