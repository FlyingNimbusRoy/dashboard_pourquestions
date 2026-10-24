<?php

namespace App\Filament\Widgets;

use App\Services\Insights\GameInsights;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Summary tiles for the Game activity page: total, lists present, orphan sessions, averages. */
class GameActivityStats extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        $s = app(GameInsights::class)->summary();

        $noListPct = $s['total'] > 0 ? round($s['without_list'] / $s['total'] * 100) : 0;

        return [
            Stat::make('Total games', number_format($s['total'])),
            Stat::make('With question list', number_format($s['with_list']))
                ->description(($s['with_list'] > 0 ? round($s['with_list'] / max($s['total'], 1) * 100) : 0).'% of games recorded questions'),
            Stat::make('No question list', number_format($s['without_list']))
                ->description("{$noListPct}% — sessions that ended before questions were loaded")
                ->descriptionColor($s['without_list'] > 0 ? 'warning' : 'gray'),
            Stat::make('Avg questions / game', (string) $s['avg_questions'])
                ->description('Avg length: '.$s['avg_length'].' (games with list only)'),
        ];
    }
}
