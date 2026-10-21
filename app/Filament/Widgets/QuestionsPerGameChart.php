<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InsightChart;
use App\Services\Insights\GameInsights;
use Filament\Widgets\ChartWidget;

class QuestionsPerGameChart extends ChartWidget
{
    use InsightChart;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Questions per game';

    protected ?string $description = 'Games by number of questions asked (games with a question list only)';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $b = app(GameInsights::class)->questionsPerGame();

        return ['labels' => array_keys($b), 'datasets' => [$this->barDataset('Games', $b)]];
    }

    protected function getOptions(): array
    {
        return $this->axisOptions();
    }
}
