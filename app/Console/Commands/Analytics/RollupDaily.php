<?php

namespace App\Console\Commands\Analytics;

use App\Models\DashRollupDaily;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RollupDaily extends Command
{
    protected $signature = 'analytics:rollup-daily
                            {--date= : Date to compute (Y-m-d); defaults to yesterday}';

    protected $description = 'Compute daily rollup stats from dash_game_sessions into dash_rollup_daily.';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))->toDateString()
            : Carbon::yesterday()->toDateString();

        $this->line("Computing rollup for {$date}…");

        $stats = DB::table('dash_game_sessions')
            ->whereDate('started_at', $date)
            ->selectRaw("
                COUNT(*)                                              AS games_total,
                SUM(status = 'completed')                             AS games_completed,
                SUM(status = 'ended_early')                          AS games_ended_early,
                SUM(status = 'abandoned')                             AS games_abandoned,
                COUNT(DISTINCT host_user_id)                          AS unique_hosts,
                SUM(player_count)                                     AS total_player_sessions,
                ROUND(AVG(NULLIF(duration_s, 0)))                    AS avg_duration_s
            ")
            ->first();

        $gamesTotal = (int) ($stats->games_total ?? 0);

        if ($gamesTotal === 0) {
            $this->info("No sessions found for {$date} — skipping.");
            return self::SUCCESS;
        }

        $replayers = DB::table('dash_game_sessions')
            ->whereDate('started_at', $date)
            ->whereNotNull('host_user_id')
            ->groupBy('host_user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get(['host_user_id'])
            ->count();

        $uniqueHosts = (int) ($stats->unique_hosts ?? 0);
        $replayRate = $uniqueHosts > 0 ? round($replayers / $uniqueHosts, 4) : null;
        $completionRate = $gamesTotal > 0 ? round((int) $stats->games_completed / $gamesTotal, 4) : null;

        DashRollupDaily::updateOrCreate(
            ['date' => $date],
            [
                'games_total'       => $gamesTotal,
                'games_completed'   => (int) ($stats->games_completed ?? 0),
                'games_ended_early' => (int) ($stats->games_ended_early ?? 0),
                'games_abandoned'   => (int) ($stats->games_abandoned ?? 0),
                'unique_hosts'      => $uniqueHosts,
                'total_player_sessions'    => (int) ($stats->total_player_sessions ?? 0),
                'avg_duration_s'    => $stats->avg_duration_s ? (int) $stats->avg_duration_s : null,
                'replay_rate'       => $replayRate,
                'completion_rate'   => $completionRate,
            ]
        );

        $this->info("Rollup for {$date}: {$gamesTotal} games, completion {$completionRate}, replay {$replayRate}.");
        return self::SUCCESS;
    }
}
