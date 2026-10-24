<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InsightChart;
use App\Services\Insights\QuestionUsage;
use Filament\Widgets\ChartWidget;

/** % of total asks that came from each difficulty level — same 0-100 scale as DifficultyDistributionChart (D10). */
class DifficultyUsageChart extends ChartWidget
{
    use InsightChart;

    protected static ?int $sort = 6;

    protected ?string $heading = 'Ask share per difficulty';

    protected ?string $description = '% of all game asks that came from each level';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = app(QuestionUsage::class)->perDifficulty();
        usort($rows, fn ($a, $b) => ($a['id'] ?? 0) <=> ($b['id'] ?? 0));
        $totalAsks = max(1, array_sum(array_column($rows, 'asks')));

        $pcts = array_map(fn ($r) => round($r['asks'] / $totalAsks * 100, 1), $rows);

        return [
            'labels' => array_column($rows, 'name'),
            'datasets' => [$this->barDataset('% of asks', $pcts)],
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
