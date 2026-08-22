<?php

namespace App\Services\SystemHealth;

use App\Models\SystemHealthRun;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class SystemHealthDashboard
{
    public function data(?string $selectedDate = null): array
    {
        $days = (int) config('system_health.dashboard_days', 30);
        $runs = SystemHealthRun::query()
            ->with('checks')
            ->where('started_at', '>=', now()->subDays($days)->startOfDay())
            ->orderBy('started_at')
            ->get();

        $latest = $runs->last() ?: SystemHealthRun::with('checks')->latest('started_at')->first();
        $currentStatus = $latest?->status ?? 'unknown';
        if ($latest && $latest->started_at->lt(now()->subMinutes((int) config('system_health.stale_after_minutes', 12)))) {
            $currentStatus = 'stale';
        }

        $daily = $runs
            ->groupBy(fn (SystemHealthRun $run) => $run->started_at->toDateString())
            ->map(fn (Collection $dayRuns, string $date) => $this->summarizeDay($dayRuns, $date))
            ->sortKeysDesc()
            ->values();

        $details = collect();
        if ($selectedDate) {
            $details = $runs
                ->filter(fn (SystemHealthRun $run) => $run->started_at->toDateString() === $selectedDate)
                ->sortByDesc('started_at')
                ->take(100)
                ->values();
        }

        return compact('latest', 'currentStatus', 'daily', 'details', 'selectedDate', 'days');
    }

    private function summarizeDay(Collection $runs, string $date): array
    {
        $outages = $runs->where('status', 'outage')->count();
        $degraded = $runs->where('status', 'degraded')->count();
        $status = $outages > 0 ? 'outage' : ($degraded > 0 ? 'degraded' : 'operational');
        $available = $runs->whereIn('status', ['operational', 'degraded'])->count();

        return [
            'date' => CarbonImmutable::parse($date),
            'status' => $status,
            'runs' => $runs->count(),
            'outages' => $outages,
            'warnings' => $runs->sum(fn (SystemHealthRun $run) => $run->checks->where('status', 'warning')->count()),
            'failed_checks' => $runs->sum(fn (SystemHealthRun $run) => $run->checks->where('status', 'failing')->count()),
            'availability' => $runs->isEmpty() ? 0 : round(($available / $runs->count()) * 100, 2),
            'average_duration_ms' => (int) round($runs->avg('duration_ms') ?? 0),
        ];
    }
}
