<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkShift;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class WorkTimeReportService
{
    public function build(CarbonInterface $from, CarbonInterface $to, ?User $selectedEmployee = null): array
    {
        $periodStart = $from->copy()->startOfDay();
        $periodEnd = $to->copy()->endOfDay();
        $employees = $this->employees($selectedEmployee);
        $employeeIds = $employees->pluck('id');

        $shifts = WorkShift::query()
            ->with('breaks')
            ->whereIn('user_id', $employeeIds)
            ->where('started_at', '<=', $periodEnd)
            ->where(fn ($query) => $query->whereNull('ended_at')->orWhere('ended_at', '>=', $periodStart))
            ->orderBy('started_at')
            ->get()
            ->groupBy('user_id');

        $rows = $employees->map(function (User $employee) use ($shifts, $periodStart, $periodEnd) {
            $employeeShifts = $shifts->get($employee->id, collect());
            $metrics = $this->metrics($employeeShifts, $periodStart, $periodEnd);

            return ['employee' => $employee] + $metrics;
        })->values();

        $selectedRow = $selectedEmployee
            ? $rows->first(fn (array $row) => (int) $row['employee']->id === (int) $selectedEmployee->id)
            : null;

        return [
            'employees' => $employees,
            'rows' => $rows,
            'selected' => $selectedRow,
            'days' => $selectedEmployee
                ? $this->dailyRows($shifts->get($selectedEmployee->id, collect()), $periodStart, $periodEnd)
                : collect(),
            'totals' => [
                'worked_seconds' => (int) $rows->sum('worked_seconds'),
                'break_seconds' => (int) $rows->sum('break_seconds'),
                'shift_count' => (int) $rows->sum('shift_count'),
                'employee_count' => (int) $rows->filter(fn (array $row) => $row['shift_count'] > 0)->count(),
            ],
        ];
    }

    public static function formatDuration(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return sprintf('%d:%02d', $hours, $minutes);
    }

    private function employees(?User $selectedEmployee): Collection
    {
        if ($selectedEmployee) {
            return collect([$selectedEmployee->loadMissing('role')]);
        }

        return User::query()
            ->with('role')
            ->orderBy('name')
            ->get()
            ->filter(fn (User $user) => $user->isStaff())
            ->values();
    }

    private function metrics(Collection $shifts, CarbonInterface $from, CarbonInterface $to): array
    {
        $worked = 0;
        $breaks = 0;
        $firstArrival = null;
        $lastDeparture = null;
        $hasActiveShift = false;

        foreach ($shifts as $shift) {
            [$shiftWorked, $shiftBreak] = $this->clippedSeconds($shift, $from, $to);
            if ($shiftWorked === 0 && $shiftBreak === 0) {
                continue;
            }
            $worked += $shiftWorked;
            $breaks += $shiftBreak;
            $arrival = $shift->started_at->greaterThan($from) ? $shift->started_at : $from;
            $departure = $shift->ended_at && $shift->ended_at->lessThan($to) ? $shift->ended_at : ($shift->ended_at ? $to : null);
            $firstArrival = ! $firstArrival || $arrival->lessThan($firstArrival) ? $arrival->copy() : $firstArrival;
            if ($departure) {
                $lastDeparture = ! $lastDeparture || $departure->greaterThan($lastDeparture) ? $departure->copy() : $lastDeparture;
            } else {
                $hasActiveShift = true;
            }
        }

        $count = $shifts->count();

        return [
            'worked_seconds' => $worked,
            'break_seconds' => $breaks,
            'shift_count' => $count,
            'average_seconds' => $count > 0 ? intdiv($worked, $count) : 0,
            'first_arrival' => $firstArrival,
            'last_departure' => $lastDeparture,
            'has_active_shift' => $hasActiveShift,
        ];
    }

    private function dailyRows(Collection $shifts, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $days = collect();
        $cursor = $from->copy()->startOfDay();

        while ($cursor->lessThanOrEqualTo($to)) {
            $dayStart = $cursor->copy()->startOfDay();
            $dayEnd = $cursor->copy()->endOfDay();
            $dayShifts = $shifts->filter(fn (WorkShift $shift) =>
                $shift->started_at->lessThanOrEqualTo($dayEnd)
                && ($shift->ended_at === null || $shift->ended_at->greaterThanOrEqualTo($dayStart))
            );
            $metrics = $this->metrics($dayShifts, $dayStart, $dayEnd);

            if ($metrics['shift_count'] > 0) {
                $arrivalMinute = $metrics['first_arrival']
                    ? ($metrics['first_arrival']->hour * 60 + $metrics['first_arrival']->minute)
                    : 0;
                $end = $metrics['has_active_shift']
                    ? (now()->greaterThan($dayEnd) ? $dayEnd : now())
                    : $metrics['last_departure'];
                $departureMinute = $end ? ($end->hour * 60 + $end->minute) : $arrivalMinute;
                $days->push($metrics + [
                    'date' => $dayStart,
                    'timeline_left' => round($arrivalMinute / 1440 * 100, 3),
                    'timeline_width' => max(0.8, round(max(1, $departureMinute - $arrivalMinute) / 1440 * 100, 3)),
                ]);
            }

            $cursor = $cursor->addDay();
        }

        return $days->reverse()->values();
    }

    private function clippedSeconds(WorkShift $shift, CarbonInterface $from, CarbonInterface $to): array
    {
        $start = $shift->started_at->greaterThan($from) ? $shift->started_at : $from;
        $rawEnd = $shift->ended_at ?? now();
        $end = $rawEnd->lessThan($to) ? $rawEnd : $to;
        if ($end->lessThanOrEqualTo($start)) {
            return [0, 0];
        }

        $gross = (int) $start->diffInSeconds($end);
        $breakSeconds = 0;
        foreach ($shift->breaks as $break) {
            $breakStart = $break->started_at->greaterThan($start) ? $break->started_at : $start;
            $rawBreakEnd = $break->ended_at ?? now();
            $breakEnd = $rawBreakEnd->lessThan($end) ? $rawBreakEnd : $end;
            if ($breakEnd->greaterThan($breakStart)) {
                $breakSeconds += (int) $breakStart->diffInSeconds($breakEnd);
            }
        }

        return [max(0, $gross - $breakSeconds), $breakSeconds];
    }
}
