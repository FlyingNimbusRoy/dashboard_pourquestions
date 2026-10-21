<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Cache;
use App\Models\Question;
use Filament\Widgets\ChartWidget;

/** Single-series magnitude chart: number of questions per difficulty level. */
class DifficultyDistributionChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Questions in bank per difficulty';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        return Cache::remember("widget.".static::class, 60, fn (): array => $this->buildData());
    }

    private function buildData(): array
    {
        $levels = range((int) config('dashboard.difficulty.min'), (int) config('dashboard.difficulty.max'));
        $counts = Question::query()->selectRaw('difficulty, count(*) as total')->groupBy('difficulty')->pluck('total', 'difficulty');

        return [
            'labels' => array_map('strval', $levels),
            'datasets' => [[
                'label' => 'Questions',
                'data' => array_map(fn (int $level) => (int) $counts->get($level, 0), $levels),
                'backgroundColor' => '#a855f7',
                'borderRadius' => 4,
                'borderSkipped' => 'start',
                'maxBarThickness' => 18,
            ]],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'x' => ['grid' => ['display' => false], 'ticks' => ['color' => '#57466e']],
                'y' => ['grid' => ['color' => 'rgba(124,58,237,0.10)'], 'ticks' => ['precision' => 0, 'color' => '#57466e'], 'beginAtZero' => true],
            ],
        ];
    }
}
