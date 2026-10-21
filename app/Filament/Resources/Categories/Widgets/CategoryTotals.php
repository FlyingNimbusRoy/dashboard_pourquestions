<?php

namespace App\Filament\Resources\Categories\Widgets;

use App\Models\Category;
use App\Models\Question;
use App\Services\Insights\QuestionUsage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Totals only; the per-category balance chart lives on the dashboard. */
class CategoryTotals extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $usage = app(QuestionUsage::class);
        $total = $usage->totalQuestions();
        $uncategorized = Question::whereNull('category_id')->count();
        $due = Question::validationDue()->count();

        return [
            Stat::make('Categories', Category::count()),
            Stat::make('Questions', $total)
                ->description($uncategorized > 0 ? "{$uncategorized} without category" : 'All categorised')
                ->color($uncategorized > 0 ? 'warning' : 'success'),
            Stat::make('Due for review', $due)
                ->description($total > 0 ? round($due / $total * 100).'% of all questions' : null)
                ->color($due > 0 ? 'warning' : 'success'),
            Stat::make('Ever asked', $total > 0 ? round($usage->distinctAsked() / $total * 100).'%' : '0%')
                ->description($usage->distinctAsked().' of '.$total.' questions'),
        ];
    }
}
