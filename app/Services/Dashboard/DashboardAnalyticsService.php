<?php

namespace App\Services\Dashboard;

use App\Models\Appointments;
use App\Models\Customer;
use App\Services\WorkTimeReportService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    public function __construct(private readonly WorkTimeReportService $workTime) {}

    public function report(DashboardPeriod $period): array
    {
        $summary = $this->appointmentSummary($period);
        $previous = $this->appointmentSummary($period->previous());
        $workTime = $this->workTime->build($period->start, $period->end);

        return [
            'period' => $period,
            'summary' => $summary,
            'comparison' => [
                'revenue' => $this->change((float) $summary['revenue'], (float) $previous['revenue']),
                'completed' => $this->change((int) $summary['completed'], (int) $previous['completed']),
                'clients' => $this->change((int) $summary['unique_clients'], (int) $previous['unique_clients']),
            ],
            'customers' => $this->customerLifecycle($period),
            'work_time' => [
                'worked_seconds' => (int) $workTime['totals']['worked_seconds'],
                'break_seconds' => (int) $workTime['totals']['break_seconds'],
                'employee_count' => (int) $workTime['totals']['employee_count'],
            ],
            'daily' => $this->dailySeries($period),
            'monthly' => $this->monthlySeries(),
            'top_services' => $this->topServices($period),
            'employees' => $this->employeePerformance($period, $workTime['rows']),
        ];
    }

    public function personalCharts(int $userId): array
    {
        $today = CarbonImmutable::today();

        return [
            'week' => $this->dailySeries(new DashboardPeriod($today->subDays(6), $today->endOfDay(), 'last_7_days'), $userId),
            'month' => $this->dailySeries(new DashboardPeriod($today->startOfMonth(), $today->endOfDay(), 'current_month'), $userId),
        ];
    }

    public function appointmentSummary(DashboardPeriod $period, ?int $userId = null): array
    {
        $row = Appointments::query()
            ->when($userId, fn (Builder $query) => $query->where('user_id', $userId))
            ->whereBetween('appointment_start', [$period->start, $period->end])
            ->where('status', '!=', 'rescheduled')
            ->selectRaw("COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show,
                SUM(CASE WHEN status = 'cancelled_by_client' THEN 1 ELSE 0 END) as cancelled_by_client,
                SUM(CASE WHEN status = 'cancelled_by_business' THEN 1 ELSE 0 END) as cancelled_by_business,
                SUM(CASE WHEN status IN ('pending','confirmed','checked_in','in_progress') THEN 1 ELSE 0 END) as active,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN price ELSE 0 END), 0) as revenue,
                COALESCE(SUM(CASE WHEN status = 'no_show' THEN price ELSE 0 END), 0) as lost_revenue,
                COALESCE(SUM(CASE WHEN status IN ('cancelled_by_client','cancelled_by_business') THEN price ELSE 0 END), 0) as cancelled_value,
                COALESCE(AVG(CASE WHEN status = 'completed' THEN price END), 0) as average_check,
                COUNT(DISTINCT customer_id) as linked_clients,
                COUNT(DISTINCT CASE WHEN customer_id IS NULL THEN COALESCE(NULLIF(LOWER(TRIM(client_email)), ''), NULLIF(TRIM(client_phone), '')) END) as guest_clients")
            ->first();

        $completed = (int) ($row?->completed ?? 0);
        $noShow = (int) ($row?->no_show ?? 0);
        $cancelledByClient = (int) ($row?->cancelled_by_client ?? 0);
        $cancelledByBusiness = (int) ($row?->cancelled_by_business ?? 0);
        $resolved = $completed + $noShow + $cancelledByClient + $cancelledByBusiness;

        return [
            'total' => (int) ($row?->total ?? 0),
            'completed' => $completed,
            'no_show' => $noShow,
            'cancelled_by_client' => $cancelledByClient,
            'cancelled_by_business' => $cancelledByBusiness,
            'cancelled' => $cancelledByClient + $cancelledByBusiness,
            'active' => (int) ($row?->active ?? 0),
            'revenue' => (float) ($row?->revenue ?? 0),
            'lost_revenue' => (float) ($row?->lost_revenue ?? 0),
            'cancelled_value' => (float) ($row?->cancelled_value ?? 0),
            'average_check' => (float) ($row?->average_check ?? 0),
            'unique_clients' => (int) ($row?->linked_clients ?? 0) + (int) ($row?->guest_clients ?? 0),
            'completion_rate' => $resolved > 0 ? round($completed / $resolved * 100, 1) : 0,
            'no_show_rate' => $resolved > 0 ? round($noShow / $resolved * 100, 1) : 0,
            'cancellation_rate' => $resolved > 0 ? round(($cancelledByClient + $cancelledByBusiness) / $resolved * 100, 1) : 0,
        ];
    }

    public function customerLifecycle(DashboardPeriod $period, ?int $userId = null): array
    {
        $asOf = $period->end->isFuture() ? CarbonImmutable::now()->endOfDay() : $period->end;
        $currentVisits = fn (Builder $query) => $query
            ->when($userId, fn (Builder $appointments) => $appointments->where('user_id', $userId))
            ->where('status', 'completed')
            ->whereBetween('appointment_start', [$period->start, $period->end]);
        $previousVisits = fn (Builder $query) => $query
            ->when($userId, fn (Builder $appointments) => $appointments->where('user_id', $userId))
            ->where('status', 'completed')
            ->where('appointment_start', '<', $period->start);

        $visited = Customer::query()->whereHas('appointments', $currentVisits);
        $new = (clone $visited)->whereDoesntHave('appointments', $previousVisits)->count();
        $returning = (clone $visited)->whereHas('appointments', $previousVisits)->count();
        $regular = $this->regularCustomers($asOf, $userId);
        $lostCutoff = $asOf->subDays(90)->endOfDay();
        $lost = Customer::query()
            ->whereHas('appointments', fn (Builder $query) => $query
                ->when($userId, fn (Builder $appointments) => $appointments->where('user_id', $userId))
                ->where('status', 'completed')
                ->where('appointment_start', '<', $lostCutoff))
            ->whereDoesntHave('appointments', fn (Builder $query) => $query
                ->when($userId, fn (Builder $appointments) => $appointments->where('user_id', $userId))
                ->where('status', 'completed')
                ->whereBetween('appointment_start', [$lostCutoff, $asOf]))
            ->whereDoesntHave('appointments', fn (Builder $query) => $query
                ->when($userId, fn (Builder $appointments) => $appointments->where('user_id', $userId))
                ->whereIn('status', Appointments::BLOCKING_STATUSES)
                ->where('appointment_start', '>', $asOf))
            ->count();
        $registered = Customer::query()
            ->whereNotNull('password')
            ->whereBetween('account_registered_at', [$period->start, $period->end])
            ->when($userId, fn (Builder $query) => $query->whereHas('appointments', fn (Builder $appointments) => $appointments->where('user_id', $userId)))
            ->count();
        $totalVisitors = $new + $returning;

        return [
            'new' => $new,
            'returning' => $returning,
            'regular' => $regular,
            'lost' => $lost,
            'registered' => $registered,
            'retention_rate' => $totalVisitors > 0 ? round($returning / $totalVisitors * 100, 1) : 0,
        ];
    }

    private function regularCustomers(CarbonImmutable $asOf, ?int $userId = null): int
    {
        $query = Customer::query();

        for ($monthsAgo = 0; $monthsAgo < 3; $monthsAgo++) {
            $month = $asOf->subMonthsNoOverflow($monthsAgo);
            $query->whereHas('appointments', fn (Builder $appointments) => $appointments
                ->when($userId, fn (Builder $scoped) => $scoped->where('user_id', $userId))
                ->where('status', 'completed')
                ->whereBetween('appointment_start', [$month->startOfMonth(), $month->endOfMonth()]));
        }

        return $query->count();
    }

    private function dailySeries(DashboardPeriod $period, ?int $userId = null): array
    {
        $rows = Appointments::query()
            ->when($userId, fn (Builder $query) => $query->where('user_id', $userId))
            ->whereBetween('appointment_start', [$period->start, $period->end])
            ->where('status', '!=', 'rescheduled')
            ->selectRaw("DATE(appointment_start) as report_date,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show,
                SUM(CASE WHEN status IN ('cancelled_by_client','cancelled_by_business') THEN 1 ELSE 0 END) as cancelled,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN price ELSE 0 END), 0) as revenue")
            ->groupByRaw('DATE(appointment_start)')
            ->orderBy('report_date')
            ->get()
            ->keyBy('report_date');

        $series = [];
        for ($day = $period->start->startOfDay(); $day->lte($period->end); $day = $day->addDay()) {
            $row = $rows->get($day->toDateString());
            $series[] = [
                'date' => $day->format('d.m'),
                'completed' => (int) ($row?->completed ?? 0),
                'no_show' => (int) ($row?->no_show ?? 0),
                'cancelled' => (int) ($row?->cancelled ?? 0),
                'revenue' => (float) ($row?->revenue ?? 0),
            ];
        }

        return $series;
    }

    private function monthlySeries(int $months = 12, ?int $userId = null): array
    {
        $end = CarbonImmutable::today()->endOfMonth();
        $start = $end->subMonthsNoOverflow($months - 1)->startOfMonth();
        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', appointment_start)"
            : "DATE_FORMAT(appointment_start, '%Y-%m')";
        $rows = Appointments::query()
            ->when($userId, fn (Builder $query) => $query->where('user_id', $userId))
            ->whereBetween('appointment_start', [$start, $end])
            ->where('status', '!=', 'rescheduled')
            ->selectRaw("{$monthExpression} as report_month,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show,
                SUM(CASE WHEN status IN ('cancelled_by_client','cancelled_by_business') THEN 1 ELSE 0 END) as cancelled,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN price ELSE 0 END), 0) as revenue")
            ->groupByRaw($monthExpression)
            ->orderBy('report_month')
            ->get()
            ->keyBy('report_month');

        $series = [];
        for ($month = $start; $month->lte($end); $month = $month->addMonth()) {
            $row = $rows->get($month->format('Y-m'));
            $series[] = [
                'month' => $month->locale(app()->getLocale())->translatedFormat('M Y'),
                'completed' => (int) ($row?->completed ?? 0),
                'no_show' => (int) ($row?->no_show ?? 0),
                'cancelled' => (int) ($row?->cancelled ?? 0),
                'revenue' => (float) ($row?->revenue ?? 0),
            ];
        }

        return $series;
    }

    private function topServices(DashboardPeriod $period): Collection
    {
        return Appointments::query()
            ->where('status', 'completed')
            ->whereBetween('appointment_start', [$period->start, $period->end])
            ->whereNotNull('service_id')
            ->select('service_id')
            ->selectRaw('COUNT(*) as completed_count, COALESCE(SUM(price), 0) as revenue')
            ->groupBy('service_id')
            ->orderByDesc('revenue')
            ->limit(8)
            ->with('service.translations')
            ->get();
    }

    private function employeePerformance(DashboardPeriod $period, Collection $workRows): Collection
    {
        $appointmentRows = Appointments::query()
            ->whereBetween('appointment_start', [$period->start, $period->end])
            ->whereNotNull('user_id')
            ->where('status', '!=', 'rescheduled')
            ->select('user_id')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show,
                SUM(CASE WHEN status IN ('cancelled_by_client','cancelled_by_business') THEN 1 ELSE 0 END) as cancelled,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN price ELSE 0 END), 0) as revenue")
            ->groupBy('user_id')
            ->with('user:id,name,profile_photo_path')
            ->get()
            ->keyBy('user_id');

        return $workRows->map(function (array $work) use ($appointmentRows) {
            $employee = $work['employee'];
            $appointments = $appointmentRows->get($employee->id);

            return [
                'employee' => $employee,
                'completed' => (int) ($appointments?->completed ?? 0),
                'no_show' => (int) ($appointments?->no_show ?? 0),
                'cancelled' => (int) ($appointments?->cancelled ?? 0),
                'revenue' => (float) ($appointments?->revenue ?? 0),
                'worked_seconds' => (int) $work['worked_seconds'],
            ];
        })->sortByDesc('revenue')->values();
    }

    private function change(float|int $current, float|int $previous): array
    {
        return [
            'absolute' => round($current - $previous, 2),
            'percent' => $previous == 0
                ? ($current == 0 ? 0 : null)
                : round(($current - $previous) / abs($previous) * 100, 1),
        ];
    }
}
