<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Cache;
use App\Enums\ReviewOutcome;
use App\Models\QuestionReview;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/** Single-series time chart: questions reviewed (approved or needs fix) per week, last 12 weeks. */
class ValidationProgressChart extends ChartWidget
{
    private const WEEKS = 12;

    protected static ?int $sort = 3;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Questions reviewed per week';

    protected ?string $description = 'Last 12 weeks, skipped questions excluded';

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
        $firstWeek = now()->startOfWeek()->subWeeks(self::WEEKS - 1);

        $counts = QuestionReview::query()
            ->whereIn('outcome', [ReviewOutcome::Approved, ReviewOutcome::NeedsFix])
            ->where('reviewed_at', '>=', $firstWeek)
            ->pluck('reviewed_at')
            ->countBy(fn (Carbon $at) => $at->copy()->startOfWeek()->toDateString());

        $labels = [];
        $data = [];
        for ($i = 0; $i < self::WEEKS; $i++) {
            $week = $firstWeek->copy()->addWeeks($i);
            $labels[] = $week->format('M j');
            $data[] = $counts->get($week->toDateString(), 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Reviewed',
                'data' => $data,
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
