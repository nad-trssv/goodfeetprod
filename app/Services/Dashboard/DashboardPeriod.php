<?php

namespace App\Services\Dashboard;

use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final readonly class DashboardPeriod
{
    public function __construct(
        public CarbonImmutable $start,
        public CarbonImmutable $end,
        public string $preset,
    ) {}

    public static function from(array $filters): self
    {
        $preset = $filters['period'] ?? 'current_month';
        $today = CarbonImmutable::today();

        [$start, $end] = match ($preset) {
            'last_30_days' => [$today->subDays(29)->startOfDay(), $today->endOfDay()],
            'previous_month' => [$today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()],
            'custom' => [
                CarbonImmutable::parse($filters['from'])->startOfDay(),
                CarbonImmutable::parse($filters['to'])->endOfDay(),
            ],
            default => [$today->startOfMonth(), $today->endOfDay()],
        };

        if ($start->diffInDays($end) > 366) {
            throw ValidationException::withMessages([
                'to' => __('admin_dashboard.validation_period_too_long'),
            ]);
        }

        return new self($start, $end, $preset);
    }

    public function previous(): self
    {
        $days = $this->start->diffInDays($this->end) + 1;
        $end = $this->start->subSecond();

        return new self($end->subDays($days - 1)->startOfDay(), $end, 'comparison');
    }

    public function days(): int
    {
        return $this->start->diffInDays($this->end) + 1;
    }
}
