<?php

namespace App\Services;

use App\Http\Requests\AppointmentRequest;
use App\Models\Appointments;
use App\Models\RedDay;
use App\Models\Services;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;

class AppointmentService
{
    /**
     * Create a new class instance.
     */
    public function list($adminRole, $masterId = null)
    {
        $userId = Auth::user()->id;
        try {
            if ($adminRole == 'superAdmin') {
                $appointments = Appointments::with('media')->orderByDesc('appointment_start')->get();
            } elseif ($adminRole == 'byMaster' && $masterId) {
                $appointments = Appointments::with('media')->where('user_id', $masterId)->orderByDesc('appointment_start')->get();
            } else {
                $appointments = Appointments::with('media')->where('user_id', $userId)->orderByDesc('appointment_start')->get();
            }

            $users = User::all();
            if ($adminRole == 'byMaster' && $masterId) {
                $redDays = RedDay::where('full_day', 1)->visibleFor($masterId)->get();
                $redTimes = RedDay::where('full_day', 0)->visibleFor($masterId)->get();
            } else {
                $redDays = RedDay::where('full_day', 1)->visibleFor($userId)->get();
                $redTimes = RedDay::where('full_day', 0)->visibleFor($userId)->get();
            }
            $today = Carbon::now()->toDateString();

            $services = Services::with(['users', 'rules', 'futureRules'])
                ->where('is_deleted', 0)
                ->get()
                ->map(function ($service) use ($today) {
                    $ruleToday = $service->ruleForDate($today);
                    $service->effective_price = $service->effectivePriceForDate($today);
                    $service->effective_duration_minutes = $ruleToday?->duration_minutes ?? $service->duration_minutes;
                    $service->next_rule = $service->futureRules
                        ? $service->futureRules->sortBy('valid_from')->first()
                        : null;
                    return $service;
                });

            $deletedServices = Services::with(['users', 'rules', 'futureRules'])
                ->where('is_deleted', 1)
                ->get()
                ->map(function ($service) use ($today) {
                    $ruleToday = $service->ruleForDate($today);
                    $service->effective_price = $service->effectivePriceForDate($today);
                    $service->effective_duration_minutes = $ruleToday?->duration_minutes ?? $service->duration_minutes;
                    $service->next_rule = $service->futureRules
                        ? $service->futureRules->sortBy('valid_from')->first()
                        : null;
                    return $service;
                });

            $events = [];
            $closedDays = [];

            foreach ($appointments as $appint) {
                $event = [
                    'id' => $appint->id,
                    'editable' => true,
                    'title' => $appint->service->name,
                    'appointment_time_from' => $appint->service->duration_minutes_min,
                    'appointment_time_to' => $appint->service->duration_minutes,
                    'user_id' => $appint->user_id,
                    'client_phone' => $appint->client_phone,
                    'client_name' => $appint->client_name,
                    'client_lastname' => $appint->client_lastname,
                    'service_id' => $appint->service_id,
                    'textColor' => $appint->service->eventColor,
                    'masterThumb' => $appint->user->profile_photo_url,
                    'description' => $appint->description,
                    'price' => $appint->price,
                    'price_can_change' => $appint->service->price_can_change,
                    // FullCalendar v3 must receive a timezone-less local value.
                    // Serializing Carbon directly converts it to UTC and shifts
                    // the displayed hour in the browser.
                    'start' => $appint->appointment_start->format('Y-m-d\TH:i:s'),
                    'end' => $appint->appointment_end->format('Y-m-d\TH:i:s'),
                    'master' => $appint->user->name,
                    'masterUsername' => $appint->user->username,
                    'media' => $appint->media,
                ];
                $events[] = $event;
                $closedDays[] = $event;
            }

            foreach ($redTimes as $item) {
                if (!$item->start_time || !$item->end_time) {
                    continue;
                }
                $date = $item->date;
                $dateCarbon = Carbon::parse($date);
                $start = $dateCarbon->copy()->setTimeFromTimeString($item->start_time);
                $end = $dateCarbon->copy()->setTimeFromTimeString($item->end_time);

                $closedDays[] = [
                    'id' => 'closedTime-' . $item->id,
                    'editable' => false,
                    'title' => $item->name,
                    'appointment_time_from' => null,
                    'appointment_time_to' => null,
                    'user_id' => 1,
                    'client_phone' => '',
                    'client_name' => '',
                    'client_lastname' => '',
                    'service_id' => '',
                    'textColor' => '#b91212',
                    'masterThumb' => '',
                    'description' => '',
                    'price' => '',
                    'price_can_change' => '',
                    'start' => $start->toDateTimeString(),
                    'end' => $end->toDateTimeString(),
                    'master' => '',
                    'masterUsername' => '',
                    'media' => null,
                ];
            }

            return [
                'closedDays' => $closedDays,
                'appointments' => $events,
                'services' => $services,
                'deletedServices' => $deletedServices,
                'users' => $users,
                'redDays' => $redDays,
            ];

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function store(AppointmentRequest $request)
    {
        try {
            $startTime = \Carbon\Carbon::parse($request->appointment_start)->format('H:i');
            $endTime = \Carbon\Carbon::parse($request->appointment_end)->format('H:i');
            $date = \Carbon\Carbon::parse($request->appointment_start)->format('Y-m-d');
            
            $roomId = app(\App\Services\Booking\RoomAllocationService::class)
                ->assign($request->user_id, $date, $startTime, $endTime);

            if ($roomId === null && \App\Models\User::with('rooms')->find($request->user_id)?->rooms->isNotEmpty()) {
                throw new Exception('На это время нет свободных мест в кабинетах. Выберите другое время.');
            }
                        
            $events = Appointments::create(array_merge($request->validated(), ['room_id' => $roomId]));
            return $events;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function update(AppointmentRequest $request)
    {
        try {
            $appointment = Appointments::find($request->id);
            
            $startTime = \Carbon\Carbon::parse($request->appointment_start)->format('H:i');
            $endTime = \Carbon\Carbon::parse($request->appointment_end)->format('H:i');
            $date = \Carbon\Carbon::parse($request->appointment_start)->format('Y-m-d');
            
            // Сбрасываем room_id текущей записи чтобы не мешала при расчёте
            $appointment->update(['room_id' => null]);
            
            $roomId = app(\App\Services\Booking\RoomAllocationService::class)
                ->assign($request->user_id, $date, $startTime, $endTime);

            if ($roomId === null && \App\Models\User::with('rooms')->find($request->user_id)?->rooms->isNotEmpty()) {
                throw new Exception('На это время нет свободных мест в кабинетах. Выберите другое время.');
            }
            
            $appointment->update(array_merge($request->validated(), ['room_id' => $roomId]));
            return $appointment;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function show(Appointments $appointment)
    {
        try {
            $appointment->title = $appointment->service->name;
            
            return $appointment;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
