<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InsightChart;
use App\Services\Insights\QuestionUsage;
use Filament\Widgets\ChartWidget;

class CategoryCoverageChart extends ChartWidget
{
    use InsightChart;

    protected static ?int $sort = 4;

    protected ?string $heading = 'Questions asked per category';

    protected ?string $description = '% of the category that has been asked in at least one game';

    protected ?string $maxHeight = '420px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = collect(app(QuestionUsage::class)->perCategory())->where('questions', '>', 0)->sortBy('pct')->values();

        return [
            'labels' => $rows->map(fn ($r) => "{$r['name']} ({$r['asked']}/{$r['questions']})")->all(),
            'datasets' => [$this->barDataset('% asked', $rows->pluck('pct')->all())],
        ];
    }

    protected function getOptions(): array
    {
        return $this->axisOptions(horizontal: true, max: 100);
    }
}
