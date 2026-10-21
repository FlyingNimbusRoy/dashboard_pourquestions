<?php

namespace App\Filament\Widgets\Concerns;

/** Shared single-hue chart styling (purple magnitude bars, soft grid, no legend). */
trait InsightChart
{
    protected function barDataset(string $label, array $data, ?array $colors = null): array
    {
        return [
            'label' => $label,
            'data' => array_values($data),
            'backgroundColor' => $colors ?? config('dashboard.insights.chart_color'),
            'borderRadius' => 4,
            'borderSkipped' => 'start',
            'maxBarThickness' => 18,
        ];
    }

    protected function axisOptions(bool $horizontal = false, ?int $max = null, bool $percent = false): array
    {
        $grid = ['color' => 'rgba(124,58,237,0.10)'];
        $value = ['grid' => $grid, 'beginAtZero' => true, 'ticks' => ['precision' => 0, 'color' => '#57466e']]
            + ($max ? ['max' => $max] : []);
        $cat = ['grid' => ['display' => false], 'ticks' => ['color' => '#57466e']];

        return [
            'indexAxis' => $horizontal ? 'y' : 'x',
            'plugins' => ['legend' => ['display' => false]],
            'scales' => $horizontal ? ['x' => $value, 'y' => $cat] : ['x' => $cat, 'y' => $value],
        ];
    }
}
