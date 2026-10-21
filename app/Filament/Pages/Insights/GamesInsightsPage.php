<?php

namespace App\Filament\Pages\Insights;

use App\Services\Insights\GameInsights;
use App\Services\Insights\QuestionUsage;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class GamesInsightsPage extends Page
{
    protected string $view = 'filament.pages.insights.games-insights';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Game activity';

    protected static ?string $title = 'Game activity';

    protected static ?string $slug = 'insights/game-activity';

    protected function getHeaderWidgets(): array
    {
        return [\App\Filament\Widgets\GamesPerMonthChart::class, \App\Filament\Widgets\QuestionsPerGameChart::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $g = app(GameInsights::class);

        return [
            'summary' => $g->summary(),
            'lengths' => $g->lengths(),
            'months' => $g->perMonth(12),
            'gamepacks' => collect(app(QuestionUsage::class)->perGamepack())->sortByDesc('asks')->values()->all(),
        ];
    }
}
