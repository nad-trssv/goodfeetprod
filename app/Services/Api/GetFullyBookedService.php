<?php

namespace App\Services\Api;

use App\Http\Requests\BookingRequest;
use App\Models\AppointmentMedia;
use App\Models\Appointments;
use App\Models\RedDay;
use App\Models\Services;
use App\Models\SiteSettings;
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
            return DB::transaction(function () use ($request, $startDateTime, $endDateTime ) {
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
        
    function getBusyDays($request){
        $userId = $request->user_id;
        
        $limit = $this->getBookingLimit();
        $days = (int) $limit['days'];
    
        $today = Carbon::now()->format('Y-m-d');  
        $limitDay = Carbon::now()->addDays($days)->format('Y-m-d');
    
        $busyDays = [];
        
        for ($date = Carbon::parse($today); $date->lte(Carbon::parse($limitDay)); $date->addDay()) {
            $day = $date->format('Y-m-d');
            $workingHours = $this->getWorkHours($day);
    
            $duration = $this->getServiceDuration($request->service_id, $day);
            
            if ($workingHours && $workingHours['start'] !== null && $workingHours['end'] !== null) {
                $bookedEvents = $this->getBookedEvents($userId, $day);
                $availableSlots = $this->getAvailableSlots($day, $request->service_id, $userId);
    
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
            }
        }
        
        return $busyDays;
    }
    
    function getWorkHours($date){
        $timestamp = strtotime($date);
        $dayOfWeek = date('l', $timestamp);

        $siteSettings = SiteSettings::where('group', 'hours')
        ->where('key', 'work_hours')
        ->first();

        if ($siteSettings) {
            $workHours = json_decode($siteSettings->payload, true);
            if (isset($workHours[strtolower($dayOfWeek)])) {
                return $workHours[strtolower($dayOfWeek)];
            } else {
                return null;
            }
        }
    }
    function getLunchHours(){
        $siteSettings = SiteSettings::where('group', 'hours')
        ->where('key', 'lunch_hours')
        ->first();

        if ($siteSettings) {
            return json_decode($siteSettings->payload, true);
        }
    }
    function getBookingLimit() {
        $siteSettings = SiteSettings::where('group', 'hours')
        ->where('key', 'booking_date_limit')
        ->first();

        if ($siteSettings) {
            return json_decode($siteSettings->payload, true);
        }
    }
    function getRedDays($date) {
        $reddays = RedDay::where('full_day', 0)
        ->where('date', $date)
        ->get();

        if ($reddays && $reddays instanceof \Illuminate\Database\Eloquent\Collection) {
            return $reddays->toArray(); 
        }

        return [];
    }
    function getServiceDuration($serviceId, $date) {
        $service = Services::with('rules')->find($serviceId);
    
        if (!$service) {
            return null;
        }
    
        $rule = $service->ruleForDate($date);
    
        if ($rule && $rule->duration_minutes) {
            return (int) $rule->duration_minutes;
        }
    
        return (int) $service->duration_minutes;
    }
    
    function getBookedEvents($userId, $date){
        if($userId !== 'all'){
            $appointments = Appointments::where('user_id', $userId)->whereDate('appointment_start', $date)->get();
        }else {
            $appointments = Appointments::whereDate('appointment_start', $date)->get();
        }
    
        $events = array();
        foreach($appointments as $appint)
        {
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
        
        $isRedDay = RedDay::where('full_day', 1)
        ->where('date', $date)
        ->get();

        if ($isRedDay->isNotEmpty()) {
            return true; 
        }       
        return false;
    }
    
    function getAvailableSlots($date, $serviceId, $userId) {
            $workHours = $this->getWorkHours($date);
            $lunchHours = $this->getLunchHours();
            $redDays = $this->getRedDays($date);

            $bookedEvents = $this->getBookedEvents($userId, $date);
            $serviceDuration = $this->getServiceDuration($serviceId, $date);
        
            $fixedBooking = SiteSettings::where('group', 'hours')->where('key', 'fixed_booking_hours')->value('payload');
            $dataArray = json_decode($fixedBooking, true);
            $isFixedBooking = isset($dataArray['value']) && $dataArray['value'] == 1;
            $fixedSlots = $isFixedBooking ? $dataArray['payload'] : [];
            
            $availableSlots = [];
            $now = Carbon::now();
            $oneHourLater = $now->copy()->addHour(); 
            $isToday = Carbon::parse($date)->isToday(); 
        
            $startTime = Carbon::parse($workHours['start']);
            $endTime = Carbon::parse($workHours['end']);
            
            if(!$lunchHours['start'] === null || !$lunchHours['end'] === null) {
                $lunchStart = Carbon::parse($lunchHours['start']);
                $lunchEnd = Carbon::parse($lunchHours['end']);
            }else{
                $lunchStart = null;
                $lunchEnd = null;
            }
        
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
        
                    if (isset($redDayStart) && $slotEnd->gt($redDayStart) && $slotStart->lt($redDayEnd)) {
                        continue;
                    }
        
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
                        $availableSlots[] = [
                            'start' => $slotStart->format('H:i'),
                            'end' => $slotEnd->format('H:i'),
                        ];
                    }
                }
                return $availableSlots;
            }
        
            $rangeStart = $startTime;
            $rangeEnd = $endTime;
        
            usort($bookedEvents, function ($a, $b) {
                return Carbon::parse($a['start'])->timestamp - Carbon::parse($b['start'])->timestamp;
            });
        
            for ($time = $rangeStart->copy(); $time->lt($rangeEnd); $time->addMinutes(30)) {
                $slotStart = $time->copy();
                $slotEnd = $slotStart->copy()->addMinutes($serviceDuration);
        
                if ($isToday) {
                    if ($slotStart->lt($now)) {
                        continue;
                    }
                }
        
                if ($slotEnd->gt($rangeEnd)) {
                    continue;
                }
        
                if($lunchStart && $lunchEnd) {
                    if ($slotStart->lt($lunchEnd) && $slotEnd->gt($lunchStart)) {
                        continue; 
                    }
                }
                
                foreach($redDayArray as $redDay) {
                    if ($slotStart->lt($redDay['end_time']) && $slotEnd->gt($redDay['start_time'])) {
                        continue 2; 
                    }
                }
                $isAvailable = true;
                foreach ($bookedEvents as $event) {
                    $eventStart = Carbon::parse($event['start'])->format('H:i'); 
                    $eventEnd = Carbon::parse($event['end'])->format('H:i');     
                    $slotStartTime = $slotStart->format('H:i');                 
                    $slotEndTime = $slotEnd->format('H:i');                     
                    if (
                        $slotStartTime < $eventEnd && $slotEndTime > $eventStart
                    ) {
                        $isAvailable = false;break;
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

    function getAvailableSlotsForFixed($date, $serviceId, $userId) {
        $fixedTimeCheck = Services::where('id', $serviceId)->first(['time_from', 'time_to']);
        
        $workHours = [
            'start' => $fixedTimeCheck->time_from,
            'end' => $fixedTimeCheck->time_to
        ];
    
        $lunchHours = $this->getLunchHours();
        $redDays = $this->getRedDays($date);
        $bookedEvents = $this->getBookedEvents($userId, $date);
        $serviceDuration = $this->getServiceDuration($serviceId, $date);
        $availableSlots = [];
    
        $startTime = strtotime($workHours['start']);
        $endTime = strtotime($workHours['end']);
    
        $lunchStart = strtotime($lunchHours['start']);
        $lunchEnd = strtotime($lunchHours['end']);
    
        if ($redDays) {
            $redDayStart = strtotime($redDays['start_time']);
            $redDayEnd = strtotime($redDays['end_time']);
        }
    
        usort($bookedEvents, function ($a, $b) {
            return strtotime($a['start']) - strtotime($b['start']);
        });
    
        if (isset($redDayStart) && isset($redDayEnd)) {
            for ($time = $redDayStart; $time < $redDayEnd; $time += 1800) {
                $slotStart = date('H:i', $time);
                $slotEnd = date('H:i', $time + ($serviceDuration * 60));
    
                if (strtotime($slotEnd) > $redDayEnd) continue;
                if ($time < $lunchEnd && strtotime($slotEnd) > $lunchStart) continue;
    
                $isAvailable = true;
                foreach ($bookedEvents as $event) {
                    if (strtotime($slotStart) < strtotime($event['end']) && strtotime($slotEnd) > strtotime($event['start'])) {
                        $isAvailable = false;
                        break;
                    }
                }
    
                if ($isAvailable) {
                    $availableSlots[] = [
                        'start' => $slotStart,
                        'end' => $slotEnd,
                    ];
                }
            }
        } else {
            for ($time = $startTime; $time < $endTime; $time += 1800) {
                $slotStart = date('H:i', $time);
                $slotEnd = date('H:i', $time + ($serviceDuration * 60));
    
                if (strtotime($slotEnd) > $endTime) continue;
                if ($time < $lunchEnd && strtotime($slotEnd) > $lunchStart) continue;
    
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
                    $availableSlots[] = [
                        'start' => $slotStart,
                        'end' => $slotEnd,
                    ];
                }
            }
        }
    
        return $availableSlots;
    }

}
