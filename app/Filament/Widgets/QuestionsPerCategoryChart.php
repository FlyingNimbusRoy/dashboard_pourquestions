<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Cache;
use App\Models\Category;
use App\Models\Question;
use Filament\Widgets\ChartWidget;

/** Single-series magnitude chart: horizontal bars, one hue, sorted, long tail folded into "Other". */
class QuestionsPerCategoryChart extends ChartWidget
{
    private const SHOWN = 15;

    protected static ?int $sort = 5;

    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Questions per category';

    protected ?string $maxHeight = '420px';

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
        $counts = Category::withCount('questions')->orderByDesc('questions_count')->get()->pluck('questions_count', 'name');

        $shown = $counts->take(self::SHOWN);
        $other = $counts->skip(self::SHOWN)->sum() + Question::whereNull('category_id')->count();
        if ($other > 0) {
            $shown->put('Other / none', $other);
        }

        return [
            'labels' => $shown->keys()->all(),
            'datasets' => [[
                'label' => 'Questions',
                'data' => $shown->values()->all(),
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
            'indexAxis' => 'y',
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'x' => ['grid' => ['color' => 'rgba(124,58,237,0.10)'], 'ticks' => ['precision' => 0, 'color' => '#57466e']],
                'y' => ['grid' => ['display' => false], 'ticks' => ['color' => '#57466e']],
            ],
        ];
    }
}
