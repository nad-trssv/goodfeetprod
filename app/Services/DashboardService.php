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
use App\Services\Booking\TodayAppointments;

class DashboardService
{
    public function __construct(private readonly TodayAppointments $todayAppointments) {}

    /**
     * Create a new class instance.
     */
    public function index()
    {
        try {
            $getAppointments = function ($appointments) {
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
                        'room' => $appint->room ? $appint->room->name : null,
                    ];
                }
                return $events;
            };
            $getServices = function () {
                return Services::with('users')->get();
            };
            $getStats = function () {
                $userId = Auth::user()->id;
                $currentMonthStart = Carbon::now()->startOfMonth()->toDateString();
                $currentMonthEnd = Carbon::now()->endOfMonth()->toDateString();
                $previousMonthStart = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth()->toDateString();

                $calculateMonthlyDifference = function ($query, $field, $currentRange, $previousRange, $operation = 'count', $column = null) {
                    if ($operation === 'count') {
                        $currentValue = (clone $query)->whereBetween($field, $currentRange)->count();
                        $preventValue = (clone $query)->whereBetween($field, $previousRange)->count();
                    } elseif ($operation === 'sum' && $column) {
                        $currentValue = (clone $query)->whereBetween($field, $currentRange)->sum($column);
                        $preventValue = (clone $query)->whereBetween($field, $previousRange)->sum($column);
                    }
                    $difference = $currentValue - $preventValue;
                    return ($difference != 0 ? ($difference > 0 ? '+' : '') . $difference : '0');
                };

                $currentRange = [$currentMonthStart, $currentMonthEnd];
                $previousRange = [$previousMonthStart, $previousMonthEnd];

                // Статистика по конкретному мастеру
                $myQuery = Appointments::where('user_id', $userId);
                $myClients = (clone $myQuery)->whereBetween('appointment_start', $currentRange)->count();
                $mySalary = (clone $myQuery)->whereBetween('appointment_start', $currentRange)->sum('price');
                $myClientsDiff = $calculateMonthlyDifference(clone $myQuery, 'appointment_start', $currentRange, $previousRange, 'count');
                $mySalaryDiff = $calculateMonthlyDifference(clone $myQuery, 'appointment_start', $currentRange, $previousRange, 'sum', 'price');

                // Общая статистика
                $allClients = Appointments::whereBetween('appointment_start', $currentRange)->count();
                $allSalary = Appointments::whereBetween('appointment_start', $currentRange)->sum('price');
                $allClientsDiff = $calculateMonthlyDifference(Appointments::query(), 'appointment_start', $currentRange, $previousRange, 'count');
                $allSalaryDiff = $calculateMonthlyDifference(Appointments::query(), 'appointment_start', $currentRange, $previousRange, 'sum', 'price');

                return [
                    'redDays' => RedDay::visibleFor($userId)->whereBetween('date', [$currentMonthStart, $currentMonthEnd])->count(),
                    // Мои
                    'clients' => $myClients,
                    'salary' => $mySalary,
                    'clientsDifference' => $myClientsDiff,
                    'salaryDifference' => $mySalaryDiff,
                    // Все
                    'all_clients' => $allClients,
                    'all_salary' => $allSalary,
                    'all_clientsDifference' => $allClientsDiff,
                    'all_salaryDifference' => $allSalaryDiff,
                ];
            };
            $getChartDataByDay = function () {
                $userId = Auth::user()->id;

                // Мои данные
                $myData = Appointments::where('user_id', $userId)
                    ->orderBy('appointment_start')
                    ->get(['price', 'appointment_start'])
                    ->groupBy(function ($item) {
                        return Carbon::parse($item->appointment_start)->format('Y-m-d');
                    })
                    ->map(function ($group, $date) {
                        return [
                            'date' => Carbon::parse($date)->format('d.m.y'),
                            'total_price' => $group->sum('price'),
                            'appointments_count' => $group->count()
                        ];
                    })
                    ->take(15)->values();

                // Все данные
                $allData = Appointments::orderBy('appointment_start')
                    ->get(['price', 'appointment_start'])
                    ->groupBy(function ($item) {
                        return Carbon::parse($item->appointment_start)->format('Y-m-d');
                    })
                    ->map(function ($group, $date) {
                        return [
                            'date' => Carbon::parse($date)->format('d.m.y'),
                            'total_price' => $group->sum('price'),
                            'appointments_count' => $group->count()
                        ];
                    })
                    ->take(15)->values();

                return [
                    'labels' => $myData->pluck('date'),
                    'data' => $myData->pluck('total_price'),
                    'counts' => $myData->pluck('appointments_count'),
                    'all_labels' => $allData->pluck('date'),
                    'all_data' => $allData->pluck('total_price'),
                    'all_counts' => $allData->pluck('appointments_count'),
                ];
            };
            $getChartDataByMonth = function () {
                $userId = Auth::user()->id;

                $myData = Appointments::where('user_id', $userId)
                    ->orderByDesc('appointment_start')
                    ->get(['price', 'appointment_start'])
                    ->groupBy(function ($item) {
                        return Carbon::parse($item->appointment_start)->format('Y-m');
                    })
                    ->map(function ($group, $month) {
                        return [
                            'month' => ucfirst(Carbon::parse($month)->locale('et')->isoFormat('MMMM YYYY')),
                            'total_price' => $group->sum('price'),
                            'appointments_count' => $group->count()
                        ];
                    })
                    ->take(12)->values();

                $allData = Appointments::orderByDesc('appointment_start')
                    ->get(['price', 'appointment_start'])
                    ->groupBy(function ($item) {
                        return Carbon::parse($item->appointment_start)->format('Y-m');
                    })
                    ->map(function ($group, $month) {
                        return [
                            'month' => ucfirst(Carbon::parse($month)->locale('et')->isoFormat('MMMM YYYY')),
                            'total_price' => $group->sum('price'),
                            'appointments_count' => $group->count()
                        ];
                    })
                    ->take(12)->values();

                return [
                    'labels' => $myData->pluck('month'),
                    'data' => $myData->pluck('total_price'),
                    'counts' => $myData->pluck('appointments_count'),
                    'all_labels' => $allData->pluck('month'),
                    'all_data' => $allData->pluck('total_price'),
                    'all_counts' => $allData->pluck('appointments_count'),
                ];
            };
            $getEvents = function () {
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
                $combined = $events->merge($birthdays)->merge($redDays)->unique('id');
                // Все события для администратора
                $allEvents = Events::where(function ($query) use ($today) {
                    $query->whereDate('date', $today->toDateString())
                        ->orWhere(function ($q) use ($today) {
                            $q->where('repeat', 1)
                                ->whereMonth('date', $today->month)
                                ->whereDay('date', $today->day);
                        });
                })->get()->map(function ($event) {
                    $event->type = 'event';
                    return $event;
                });

                $allRedDays = RedDay::where(function ($query) use ($today) {
                    $query->whereDate('date', $today->toDateString())
                        ->orWhere(function ($q) use ($today) {
                            $q->where('repeat', 1)
                                ->whereMonth('date', $today->month)
                                ->whereDay('date', $today->day);
                        });
                })->get()->map(function ($redDay) {
                    $redDay->type = 'redday';
                    $redDay->user_id = null;
                    $redDay->organized_by = null;
                    return $redDay;
                });

                $allCombined = $allEvents->merge($allRedDays)->unique('id');
                return [
                    'my' => $combined,
                    'all' => $allCombined,
                ];
            };
            $getActivity = function () {
                $appointments = Appointments::whereDate('appointment_start', Carbon::now()->toDateString())
                    ->orderBy('appointment_start')
                    ->get();

                $currentDay = strtolower(Carbon::now()->englishDayOfWeek);

                $hoursSetting = SiteSettings::where('group', 'hours')->first();
                $workHours = $hoursSetting ? json_decode($hoursSetting->payload, true) : [];

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
            };

            return [
                'appointments' => $getAppointments($this->todayAppointments->forUser(Auth::user())),
                'services' => $getServices(),
                'chartDataByDay' => $getChartDataByDay(),
                'chartDataByMonth' => $getChartDataByMonth(),
                'stats' => $getStats(),
                'events' => $getEvents(),
                'activity' => $getActivity(),
            ];

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
