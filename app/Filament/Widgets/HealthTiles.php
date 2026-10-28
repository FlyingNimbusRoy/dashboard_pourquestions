<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\InsightsUsage;
use App\Filament\Resources\Questions\QuestionResource;
use App\Services\Insights\BankInsights;
use App\Services\Insights\QuestionUsage;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** "Today" health tiles: question bank shape + usage headlines (D2 + D8). */
class HealthTiles extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Question bank health';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $h = app(BankInsights::class)->health();
        $usage = app(QuestionUsage::class);
        $never = count($usage->neverAskedIds());
        $neverPct = $h['total'] ? round($never / $h['total'] * 100) : 0;
        $threshold = (int) config('dashboard.insights.retirement_threshold');
        $retiring = count($usage->retirementCandidateIds($threshold));

        $top = $usage->top(1);
        $topAsks = $top[0]['asks'] ?? 0;
        $topId   = $top[0]['id'] ?? null;

        return [
            Stat::make('Validated', $h['validated_pct'].'%')
                ->description(number_format($h['due']).' due for review')
                ->icon(Heroicon::OutlinedShieldCheck)
                ->url(QuestionResource::getUrl('index', ['filters' => ['validation' => ['value' => 'due']]])),
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
            Stat::make('Most asked', $topAsks > 0 ? "×{$topAsks}" : '—')
                ->description($topId ? "Question #{$topId}" : 'No game data yet')
                ->icon(Heroicon::OutlinedFire)
                ->url(InsightsUsage::getUrl()),
            Stat::make('Retire?', number_format($retiring))
                ->description("Asked ≥ {$threshold} times — consider retiring")
                ->descriptionColor($retiring > 0 ? 'warning' : 'gray')
                ->icon(Heroicon::OutlinedArchiveBox)
                ->url(QuestionResource::getUrl('index', ['activeTab' => 'retirement_candidates'])),
        ];
    }
}
