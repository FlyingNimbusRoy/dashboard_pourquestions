<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InsightChart;
use App\Services\Insights\QuestionUsage;
use Filament\Widgets\ChartWidget;

class DifficultyUsageChart extends ChartWidget
{
    use InsightChart;

    protected static ?int $sort = 6;

    protected ?string $heading = 'Average times asked per difficulty';

    protected ?string $description = 'Total asks divided by questions at that level';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = app(QuestionUsage::class)->perDifficulty();

        return [
            'labels' => array_column($rows, 'name'),
            'datasets' => [$this->barDataset('Avg times asked', array_column($rows, 'avg'))],
        ];
    }

    protected function getOptions(): array
    {
        $o = $this->axisOptions();
        $o['scales']['y']['ticks']['precision'] = 1;

        return $o;
    }
}
