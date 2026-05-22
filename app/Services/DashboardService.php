<?php

namespace App\Services;

use App\Models\Appointments;
use App\Models\Events;
use App\Models\RedDay;
use App\Models\Services;
use App\Models\SiteSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    /**
     * Create a new class instance.
     */
    public function index()
    {
        try {
            function getAppointments(){
                $userId = Auth::user()->id;
                $appointments = Appointments::where('user_id', $userId)->whereDate('appointment_start', Carbon::now()->toDateString())->orderBy('appointment_start')->get();
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
                        'master' => $appint->user->name,
                    ];
                }
                return $events;
            }
            function getServices(){
                return Services::with('users')->get();
            }
            function getStats() {
                $currentMonthStart = Carbon::now()->startOfMonth()->toDateString();
                $currentMonthEnd = Carbon::now()->endOfMonth()->toDateString(); 
                $previousMonthStart = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                function calculateMonthlyDifference($query, $field,$currentRange, $previousRange, $operation = 'count', $column = null) {
                    if ($operation === 'count') {
                        $currentValue = (clone $query)->whereBetween($field, $currentRange)->count();
                        $preventValue = (clone $query)->whereBetween($field, $previousRange)->count();
                        $difference = $currentValue - $preventValue;
                    } elseif ($operation === 'sum' && $column) {
                        $currentValue = (clone $query)->whereBetween($field, $currentRange)->sum($column);
                        $preventValue = (clone $query)->whereBetween($field, $previousRange)->sum($column);
                        $difference = $currentValue - $preventValue;
                    }
                    $output = ($difference != 0 ? ($difference > 0 ? '+' : '') . $difference : '0');
                    return $output;
                }
                $currentRange = [$currentMonthStart, $currentMonthEnd];
                $previousRange = [$previousMonthStart, $previousMonthEnd];
                $clientsStats = calculateMonthlyDifference(Appointments::query(), 'appointment_start', $currentRange, $previousRange, 'count');
                $salaryStats = calculateMonthlyDifference(Appointments::query(), 'appointment_start', $currentRange, $previousRange, 'sum', 'price');
    
                return  [
                    'redDays' => RedDay::whereBetween('date', [$currentMonthStart, $currentMonthEnd])->count(),
                    'clients' => Appointments::whereBetween('appointment_start', [$currentMonthStart, $currentMonthEnd])->count(),
                    'salary' => Appointments::whereBetween('appointment_start', [$currentMonthStart, $currentMonthEnd])->sum('price'),
                    'clientsDifference' => $clientsStats,
                    'salaryDifference' => $salaryStats,
                ];
            }
            function getChartDataByDay() {
                $salesChartByDay = Appointments::orderBy('appointment_start')
                ->get(['price', 'appointment_start'])
                ->groupBy(function ($item) {
                    return Carbon::parse($item->appointment_start)->format('Y-m-d');
                })
                ->map(function ($group, $date) {
                    $formattedDate = Carbon::parse($date)->format('d.m.y');
                    return [
                        'date' => $formattedDate,
                        'total_price' => $group->sum('price'),
                        'appointments_count' => $group->count()
                    ];
                })
                ->take(15)
                ->values(); 

                return [
                    'labels' => $salesChartByDay->pluck('date'),
                    'data' => $salesChartByDay->pluck('total_price'),
                    'counts' => $salesChartByDay->pluck('appointments_count'),
                ];
            }
            function getChartDataByMonth() {
                $salesChartByMonth = Appointments::orderBy('appointment_start')
                ->get(['price', 'appointment_start'])
                ->groupBy(function ($item) {
                    return Carbon::parse($item->appointment_start)->format('Y-m');
                })
                ->map(function ($group, $month) {
                    $formattedMonth = Carbon::parse($month)->locale('et')->isoFormat('MMMM YYYY');
                    return [
                        'month' => ucfirst($formattedMonth),
                        'total_price' => $group->sum('price'),
                        'appointments_count' => $group->count()
                    ];
                })
                ->take(12)
                ->values();
            
                return [
                    'labels' => $salesChartByMonth->pluck('month'),
                    'data' => $salesChartByMonth->pluck('total_price'),
                    'counts' => $salesChartByMonth->pluck('appointments_count'),
                ];
            }
            function getEvents(){
                $today = Carbon::now();
                $userId = Auth::user()->id;

                // События — только свои
                $events = Events::where(function ($query) use ($today) {
                    $query->whereDate('date', $today->toDateString())
                        ->orWhere(function ($q) use ($today) {
                            $q->where('repeat', 1)
                                ->whereMonth('date', $today->month)
                                ->whereDay('date', $today->day);
                        });
                })->get();

                // Именинники — все пользователи
                $birthdays = Events::where('name', 'Sünnipäev')
                    ->where(function ($q) use ($today) {
                        $q->whereDate('date', $today->toDateString())
                        ->orWhere(function ($q2) use ($today) {
                            $q2->where('repeat', 1)
                                ->whereMonth('date', $today->month)
                                ->whereDay('date', $today->day);
                        });
                    })->get();

                // Нерабочие дни — только свои + общие
                $redDays = RedDay::visibleFor($userId)
                    ->where(function ($query) use ($today) {
                        $query->whereDate('date', $today->toDateString())
                        ->orWhere(function ($q) use ($today) {
                            $q->where('repeat', 1)
                            ->whereMonth('date', $today->month)
                            ->whereDay('date', $today->day);
                        });
                    })->get();

                $events = $events->map(function ($event) {
                    $event->type = 'event';
                    return $event;
                });

                $birthdays = $birthdays->map(function ($event) {
                    $event->type = 'birthday';
                    return $event;
                });

                $redDays = $redDays->map(function ($redDay) {
                    $redDay->type = 'redday';
                    $redDay->user_id = null;
                    $redDay->organized_by = null;
                    return $redDay;
                });

                return $events->merge($birthdays)->merge($redDays)->unique('id');
            }
            function getActivity(){
                $appointments = Appointments::whereDate('appointment_start', Carbon::now()->toDateString())
                    ->orderBy('appointment_start')
                    ->get();

                $currentDay = strtolower(Carbon::now()->englishDayOfWeek);

                $hoursSetting = SiteSettings::where('group', 'hours')->first();
                $workHours = json_decode($hoursSetting->payload, true);

                if (isset($workHours[$currentDay]) && $workHours[$currentDay]['start'] && $workHours[$currentDay]['end']) {
                    $workStart = Carbon::parse($workHours[$currentDay]['start']);
                    $workEnd = Carbon::parse($workHours[$currentDay]['end']);
                
                    $workingHours = [];
                    for ($hour = $workStart->copy(); $hour->lt($workEnd); $hour->addHour()) {
                        $workingHours[] = $hour->format('H:i');
                    }
                
                    $busyHours = [];
                    foreach ($appointments as $appointment) {
                        $appointmentStart = Carbon::parse($appointment->appointment_start);
                        $appointmentEnd = Carbon::parse($appointment->appointment_end);
                    
                        for ($hour = $appointmentStart->copy(); $hour->lt($appointmentEnd); $hour->addHour()) {
                            $busyHour = $hour->format('H:i');
                            if (!in_array($busyHour, $busyHours)) {
                                $busyHours[] = $busyHour; 
                            }
                        }
                    }
                
                    $totalWorkHours = count($workingHours);
                
                    $totalBusyHours = count($busyHours);
                
                    $productivityPercentage = ($totalWorkHours > 0)
                        ? round(($totalBusyHours / $totalWorkHours) * 100)
                        : 0;

                    return $productivityPercentage;
                } else {
                    if ($appointments->count() > 0) {
                        return 100;
                    } else {
                        return 0;
                    }
                }
            }

            return [
                'appointments' => getAppointments(),
                'services' => getServices(),
                'chartDataByDay' => getChartDataByDay(),
                'chartDataByMonth' => getChartDataByMonth(),
                'stats' => getStats(),
                'events' => getEvents(),
                'activity' => getActivity(),
            ];

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
