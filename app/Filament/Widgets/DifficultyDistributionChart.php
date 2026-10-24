<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Cache;
use App\Models\Question;
use Filament\Widgets\ChartWidget;

/** % of question bank per difficulty — same 0-100 scale as DifficultyUsageChart for side-by-side comparison (D10). */
class DifficultyDistributionChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Bank share per difficulty';

    protected ?string $description = '% of all questions at each level';

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
        $total  = (int) $counts->sum();

        return [
            'labels' => array_map('strval', $levels),
            'datasets' => [[
                'label' => '% of bank',
                'data' => array_map(fn (int $level) => $total > 0 ? round($counts->get($level, 0) / $total * 100, 1) : 0, $levels),
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
                'y' => ['max' => 100, 'beginAtZero' => true, 'grid' => ['color' => 'rgba(124,58,237,0.10)'], 'ticks' => ['color' => '#57466e']],
            ],
        ];
    }
}
