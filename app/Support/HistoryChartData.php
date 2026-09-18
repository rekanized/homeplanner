<?php

namespace App\Support;

use Illuminate\Support\Collection;

class HistoryChartData
{
    public static function monthly(Collection $snapshots): array
    {
        $snapshots = self::chronological($snapshots);

        return [
            'snapshots' => $snapshots,
            'series' => [
                ['label' => __('Income'), 'color' => 'var(--success)', 'values' => $snapshots->map(fn ($s) => (float) $s->total_income)->all()],
                ['label' => __('Expenses'), 'color' => 'var(--danger)', 'values' => $snapshots->map(fn ($s) => (float) $s->total_expenses)->all()],
                ['label' => __('Monthly Savings'), 'color' => 'var(--primary)', 'values' => $snapshots->map(fn ($s) => (float) $s->total_savings)->all()],
                ['label' => __('Remaining'), 'color' => 'var(--warning)', 'values' => $snapshots->map(fn ($s) => round($s->total_income - $s->total_expenses - $s->total_savings, 2))->all()],
            ],
        ];
    }

    public static function savings(Collection $snapshots): array
    {
        $snapshots = self::chronological($snapshots);

        return [
            'snapshots' => $snapshots,
            'series' => [
                ['label' => __('Total Accumulated'), 'color' => 'var(--primary)', 'values' => $snapshots->map(fn ($s) => (float) $s->total_amount)->all()],
            ],
        ];
    }

    private static function chronological(Collection $snapshots): Collection
    {
        return $snapshots->sortBy([
            ['created_at', 'asc'],
            ['id', 'asc'],
        ])->values();
    }
}
