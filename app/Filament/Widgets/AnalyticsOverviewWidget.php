<?php

namespace App\Filament\Widgets;

use App\Models\DashIssue;
use App\Models\DashRollupDaily;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsOverviewWidget extends StatsOverviewWidget
{
    /** Hidden until ingest data is available (completion/replay rates are always 0 without game sessions). */
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 3;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $rows = DashRollupDaily::query()
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->where('date', '<', now()->toDateString())
            ->get();

        $gamesTotal    = $rows->sum('games_total');
        $avgCompletion = $rows->avg('completion_rate');
        $avgReplay     = $rows->avg('replay_rate');

        $issueCounts = DashIssue::whereIn('status', ['open', 'assigned', 'in_progress'])
            ->selectRaw('type, count(*) as cnt')
            ->groupBy('type')
            ->pluck('cnt', 'type');

        $totalIssues = $issueCounts->sum();
        $issueDesc   = collect(['content', 'balance', 'tech'])
            ->map(fn ($t) => $issueCounts->get($t, 0) . ' ' . $t)
            ->implode(' · ');

        return [
            Stat::make('Games this week', $gamesTotal > 0 ? number_format($gamesTotal) : '—')
                ->description('past 7 days')
                ->icon(Heroicon::OutlinedTrophy),

            Stat::make('Completion rate', $avgCompletion !== null ? round($avgCompletion * 100) . '%' : '—')
                ->description('avg last 7 days')
                ->icon(Heroicon::OutlinedCheckCircle),

            Stat::make('Replay rate', $avgReplay !== null ? round($avgReplay * 100) . '%' : '—')
                ->description('games played again immediately')
                ->icon(Heroicon::OutlinedArrowPath),

            Stat::make('Open issues', $totalIssues > 0 ? (string) $totalIssues : 'None')
                ->description($totalIssues > 0 ? $issueDesc : 'No active issues')
                ->descriptionColor($totalIssues > 0 ? 'warning' : 'success')
                ->icon(Heroicon::OutlinedExclamationTriangle),
        ];
    }
}
