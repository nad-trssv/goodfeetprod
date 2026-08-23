<?php

namespace App\Services;

use App\Models\Appointments;
use App\Models\User;
use App\Services\Booking\TodayAppointments;
use App\Services\Dashboard\DashboardAnalyticsService;
use App\Services\Dashboard\DashboardFollowUpService;
use App\Services\Dashboard\DashboardPeriod;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function __construct(
        private readonly TodayAppointments $todayAppointments,
        private readonly DashboardAnalyticsService $analytics,
        private readonly DashboardFollowUpService $followUp,
        private readonly WorkTimeReportService $workTime,
    ) {}

    public function index(?User $viewer = null): array
    {
        $viewer ??= Auth::user();
        $today = today();
        $appointments = $this->todayAppointments->forUser($viewer);
        $month = new DashboardPeriod($today->toImmutable()->startOfMonth(), $today->toImmutable()->endOfDay(), 'current_month');
        $day = new DashboardPeriod($today->toImmutable()->startOfDay(), $today->toImmutable()->endOfDay(), 'today');
        $work = $this->workTime->build($month->start, $month->end, $viewer)['selected'];
        $activeToday = $appointments->whereIn('status', Appointments::BLOCKING_STATUSES);

        return [
            'appointments' => $appointments->map(fn (Appointments $appointment) => [
                'id' => $appointment->id,
                'title' => $appointment->service?->getTranslation(app()->getLocale(), 'name')
                    ?? $appointment->service?->name
                    ?? $appointment->title,
                'user_id' => $appointment->user_id,
                'client_phone' => $appointment->client_phone,
                'client_name' => $appointment->client_name,
                'client_lastname' => $appointment->client_lastname,
                'service_id' => $appointment->service_id,
                'textColor' => $appointment->service?->eventColor,
                'description' => $appointment->description,
                'price' => (float) $appointment->price,
                'start' => $appointment->appointment_start,
                'end' => $appointment->appointment_end,
                'status' => $appointment->status,
                'master' => $appointment->user?->name,
                'room' => $appointment->room?->name,
            ])->values()->all(),
            'today' => $this->visibleSummary($viewer, $day),
            'personal_month' => $this->analytics->appointmentSummary($month, $viewer->id),
            'personal_customers' => $this->analytics->customerLifecycle($month, $viewer->id),
            'personal_charts' => $this->analytics->personalCharts($viewer->id),
            'work_time' => [
                'worked_seconds' => (int) ($work['worked_seconds'] ?? 0),
                'break_seconds' => (int) ($work['break_seconds'] ?? 0),
            ],
            'current_appointment' => $activeToday
                ->first(fn (Appointments $appointment) => now()->betweenIncluded($appointment->appointment_start, $appointment->appointment_end)),
            'next_appointment' => $activeToday
                ->first(fn (Appointments $appointment) => $appointment->appointment_start->isFuture()),
            'action_required' => $this->followUp->unresolvedCount($viewer),
        ];
    }

    private function visibleSummary(User $viewer, DashboardPeriod $period): array
    {
        if ($viewer->hasAllAppointmentsScope()) {
            return $this->analytics->appointmentSummary($period);
        }

        return $this->analytics->appointmentSummary($period, $viewer->id);
    }
}
