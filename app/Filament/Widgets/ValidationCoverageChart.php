<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InsightChart;
use App\Services\Insights\BankInsights;
use Filament\Widgets\ChartWidget;

class ValidationCoverageChart extends ChartWidget
{
    use InsightChart;

    protected static ?int $sort = 7;

    protected ?string $heading = 'Validation coverage per category';

    protected ?string $description = '% of questions validated within the last year, least covered first';

    protected ?string $maxHeight = '420px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = collect(app(BankInsights::class)->validationByCategory());

        return [
            'labels' => $rows->pluck('name')->all(),
            'datasets' => [$this->barDataset('% validated', $rows->pluck('pct')->all())],
        ];
    }

    protected function getOptions(): array
    {
        return $this->axisOptions(horizontal: true, max: 100);
    }
}
