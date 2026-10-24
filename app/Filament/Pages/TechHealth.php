<?php

namespace App\Filament\Pages;

use App\Models\DashGameSession;
use App\Models\DashRollupDaily;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class TechHealth extends Page
{
    protected string $view = 'filament.pages.tech-health';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'Tech health';

    /** @return array<string, mixed> */
    public function getViewData(): array
    {
        $since = now()->subDays(30)->toDateString();

        // Status breakdown — last 30 days
        $statusBreakdown = DB::table('dash_game_sessions')
            ->where('started_at', '>=', $since)
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $total = $statusBreakdown->sum();

        // App version breakdown — last 30 days, top 10
        $versionBreakdown = DB::table('dash_game_sessions')
            ->where('started_at', '>=', $since)
            ->selectRaw('app_version, count(*) as cnt, round(avg(duration_s)) as avg_duration_s')
            ->groupBy('app_version')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();

        // Last phase at time of abandonment — where did games die
        $abandonedByPhase = DB::table('dash_game_sessions')
            ->where('started_at', '>=', $since)
            ->where('status', 'abandoned')
            ->selectRaw('last_phase_type, count(*) as cnt')
            ->groupBy('last_phase_type')
            ->orderByDesc('cnt')
            ->get();

        // Daily stats for the trend (last 14 rollup rows)
        $trend = DashRollupDaily::query()
            ->where('date', '>=', now()->subDays(14)->toDateString())
            ->orderBy('date')
            ->get(['date', 'games_total', 'games_abandoned', 'completion_rate']);

        // High-timeout questions — top 10 worst by timeout rate
        $highTimeout = DB::table('dash_game_question_results')
            ->selectRaw('question_id, sum(timeout_count) as timeouts, sum(correct_count + wrong_count + timeout_count) as attempts, round(sum(timeout_count) / nullif(sum(correct_count + wrong_count + timeout_count), 0) * 100, 1) as timeout_pct')
            ->groupBy('question_id')
            ->havingRaw('attempts >= 10')
            ->orderByDesc('timeout_pct')
            ->limit(10)
            ->get();

        return [
            'total'              => $total,
            'statusBreakdown'    => $statusBreakdown,
            'completionRate'     => $total > 0 ? round($statusBreakdown->get('completed', 0) / $total * 100) : null,
            'abandonRate'        => $total > 0 ? round($statusBreakdown->get('abandoned', 0) / $total * 100) : null,
            'versionBreakdown'   => $versionBreakdown,
            'abandonedByPhase'   => $abandonedByPhase,
            'trend'              => $trend,
            'highTimeout'        => $highTimeout,
            'since'              => $since,
        ];
    }
}
