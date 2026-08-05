<?php
namespace App\Services;
use App\Models\RedDay;
use App\Models\User;
class EmployeeCardService
{
    public function statistics(User $employee): array
    {
        $start = ($employee->employment_started_at ?? $employee->created_at)->copy()->startOfDay();
        $months = max(1, $start->diffInMonths(now()->endOfMonth()) + 1);
        $tenure = $start->diff(now());
        $monthStart=now()->startOfMonth(); $monthEnd=now()->endOfMonth();
        $metrics=$employee->appointments()->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' AND appointment_start BETWEEN ? AND ? THEN price ELSE 0 END),0) current_month_revenue",[$monthStart,$monthEnd])
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN price ELSE 0 END),0) total_revenue")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) completed_appointments")
            ->selectRaw("SUM(CASE WHEN status NOT IN ('cancelled_by_client','cancelled_by_business','rescheduled') AND appointment_start BETWEEN ? AND ? THEN 1 ELSE 0 END) current_month_appointments",[$monthStart,$monthEnd])
            ->selectRaw("COALESCE(AVG(CASE WHEN status = 'completed' THEN price END),0) average_check")->first();
        return [
            'current_month_revenue' => (float)$metrics->current_month_revenue,
            'average_monthly_revenue' => (float)$metrics->total_revenue / $months,
            'current_month_appointments' => (int)$metrics->current_month_appointments,
            'completed_appointments' => (int)$metrics->completed_appointments,
            'active_services' => $employee->services()->count(),
            'average_check' => (float)$metrics->average_check,
            'employment_date' => $start,
            'employment_days' => $start->diffInDays(now()),
            'employment_tenure' => collect([[$tenure->y,['год','года','лет']],[$tenure->m,['месяц','месяца','месяцев']],[$tenure->d,['день','дня','дней']]])->filter(fn($part)=>$part[0]>0)->map(fn($part)=>$this->countLabel($part[0],$part[1]))->implode(' ') ?: $this->countLabel(0,['день','дня','дней']),
        ];
    }
    public function calendar(User $employee): array
    {
        $exceptions = RedDay::query()
            ->where(fn ($query) => $query->where('user_id', $employee->id)->orWhereNull('user_id'))
            ->where(fn ($query) => $query
                ->where(fn ($openEnded) => $openEnded->whereNull('date_to')->whereDate('date', '>=', now()->startOfMonth()->toDateString()))
                ->orWhereDate('date_to', '>=', now()->startOfMonth()->toDateString()))
            ->orderBy('date')->orderBy('start_time')->get();
        $personal = $exceptions->where('user_id', $employee->id);
        $future = $personal->filter(fn (RedDay $day) => $day->endDate()->endOfDay()->gte(today()));
        $currentMonth = $personal->filter(fn (RedDay $day) => \Carbon\Carbon::parse($day->date)->lte(now()->endOfMonth()) && $day->endDate()->gte(now()->startOfMonth()));
        return [
            'personal_days' => $future->where('full_day', true)->take(5),
            'personal_hours' => $future->where('full_day', false)->take(5),
            'company_days' => $exceptions->whereNull('user_id')->take(5),
            'future_days_count' => $future->where('full_day', true)->count(),
            'future_hours_count' => $future->where('full_day', false)->count(),
            'month_days_count' => $currentMonth->where('full_day', true)->count(),
            'month_hours_count' => $currentMonth->where('full_day', false)->count(),
        ];
    }

    private function countLabel(int $number, array $forms): string
    {
        $mod100=$number%100; $mod10=$number%10;
        $form=($mod100>=11&&$mod100<=14)?$forms[2]:($mod10===1?$forms[0]:($mod10>=2&&$mod10<=4?$forms[1]:$forms[2]));
        return $number.' '.$form;
    }
}
