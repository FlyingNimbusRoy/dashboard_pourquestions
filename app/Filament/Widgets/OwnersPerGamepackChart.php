<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InsightChart;
use App\Services\Insights\GamepackOwners;
use Filament\Widgets\ChartWidget;

class OwnersPerGamepackChart extends ChartWidget
{
    use InsightChart;

    protected static ?int $sort = 10;

    protected ?string $heading = 'Owners per gamepack';

    protected ?string $description = 'Distinct players who own the pack (purchases and admin grants)';

    protected ?string $maxHeight = '360px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = collect(app(GamepackOwners::class)->perPack());

        return [
            'labels' => $rows->map(fn ($r) => $r['grants'] ? "{$r['name']} ({$r['grants']} via grant)" : $r['name'])->all(),
            'datasets' => [$this->barDataset('Owners', $rows->pluck('owners')->all())],
        ];
    }

    protected function getOptions(): array
    {
        return $this->axisOptions(horizontal: true);
    }
}
