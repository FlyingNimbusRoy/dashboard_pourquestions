<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Insights\QuestionUsagePage;
use App\Filament\Resources\Questions\QuestionResource;
use App\Services\Insights\BankInsights;
use App\Services\Insights\QuestionUsage;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** "Today" health tiles: is the question bank in shape. */
class HealthTiles extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Question bank health';

    protected function getColumns(): int
    {
        return 5;
    }

    protected function getStats(): array
    {
        $h = app(BankInsights::class)->health();
        $usage = app(QuestionUsage::class);
        $never = count($usage->neverAskedIds());
        $neverPct = $h['total'] ? round($never / $h['total'] * 100) : 0;

        return [
            Stat::make('Validated', $h['validated_pct'].'%')
                ->description(number_format($h['due']).' due for review')
                ->icon(Heroicon::OutlinedShieldCheck)
                ->url(QuestionResource::getUrl('index', ['filters' => ['validation' => ['value' => 'due']]])),
            Stat::make('Grading due', number_format($h['grading_due']))
                ->description('Not re-examined in over '.config('dashboard.grading.regrade_after_days').' days')
                ->descriptionColor($h['grading_due'] > 0 ? 'warning' : 'gray')
                ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                ->url(QuestionResource::getUrl('index', ['activeTab' => 'grading_due'])),
            Stat::make('No category', number_format($h['no_category']))
                ->description('of '.number_format($h['total']).' questions')
                ->icon(Heroicon::OutlinedTag)
                ->url(QuestionResource::getUrl('index', ['activeTab' => 'no_category'])),
            Stat::make('No gamepack', number_format($h['no_gamepack']))
                ->description('of '.number_format($h['total']).' questions')
                ->icon(Heroicon::OutlinedRectangleStack)
                ->url(QuestionResource::getUrl('index', ['activeTab' => 'no_gamepack'])),
            Stat::make('Never asked', number_format($never))
                ->description("{$neverPct}% of the bank, in no game yet")
                ->icon(Heroicon::OutlinedEyeSlash)
                ->url(QuestionResource::getUrl('index', ['activeTab' => 'never_asked'])),
        ];
    }
}
