<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InsightChart;
use App\Services\Insights\GameInsights;
use Filament\Widgets\ChartWidget;

class GamesPerMonthChart extends ChartWidget
{
    use InsightChart;

    protected static ?int $sort = 3;

    protected ?string $heading = 'Games per month';

    protected ?string $description = 'Last 12 months';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $months = app(GameInsights::class)->perMonth(12);
        $color = config('dashboard.insights.chart_color');

        return [
            'labels' => array_map(fn ($m) => \Illuminate\Support\Carbon::parse($m.'-01')->format('M y'), array_keys($months)),
            'datasets' => [[
                'label' => 'Games', 'data' => array_values($months),
                'borderColor' => $color, 'backgroundColor' => 'rgba(168,85,247,0.12)', 'fill' => true,
                'tension' => 0.3, 'pointBackgroundColor' => $color, 'pointRadius' => 3,
            ]],
        ];
    }

    protected function getOptions(): array
    {
        return $this->axisOptions();
    }
}
