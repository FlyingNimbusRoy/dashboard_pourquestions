<?php

namespace Tests\Feature;

use App\Models\DashRollupDaily;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AnalyticsRollupTest extends TestCase
{
    use RefreshDatabase;

    private function seedSession(string $date, string $status, int $hostId = 1, int $durationS = 600): void
    {
        DB::table('dash_game_sessions')->insert([
            'game_id'     => rand(1, 9999999),
            'pin'         => 'TEST1',
            'host_user_id' => $hostId,
            'status'      => $status,
            'app_version' => '1.0.0',
            'player_count' => 4,
            'duration_s'  => $durationS,
            'settings'    => '{}',
            'payload'     => '{}',
            'started_at'  => $date.' 20:00:00',
            'ingested_at' => now(),
        ]);
    }

    public function test_rollup_aggregates_sessions_for_the_given_date(): void
    {
        $date = '2026-10-01';
        $this->seedSession($date, 'completed', 1);
        $this->seedSession($date, 'completed', 2);
        $this->seedSession($date, 'abandoned', 3);

        $this->artisan('analytics:rollup-daily', ['--date' => $date])->assertSuccessful();

        $row = DashRollupDaily::where('date', $date)->firstOrFail();
        $this->assertSame(3, (int) $row->games_total);
        $this->assertSame(2, (int) $row->games_completed);
        $this->assertSame(1, (int) $row->games_abandoned);
        $this->assertSame(3, (int) $row->unique_hosts);
        $this->assertEqualsWithDelta(round(2 / 3, 4), (float) $row->completion_rate, 0.0001);
    }

    public function test_rollup_is_idempotent_via_update_or_create(): void
    {
        $date = '2026-10-02';
        $this->seedSession($date, 'completed', 1);
        $this->artisan('analytics:rollup-daily', ['--date' => $date])->assertSuccessful();

        // A second session added; running again should update the row, not duplicate it
        $this->seedSession($date, 'abandoned', 2);
        $this->artisan('analytics:rollup-daily', ['--date' => $date])->assertSuccessful();

        $this->assertSame(1, DashRollupDaily::where('date', $date)->count());
        $this->assertSame(2, (int) DashRollupDaily::where('date', $date)->value('games_total'));
    }

    public function test_rollup_skips_date_with_no_sessions(): void
    {
        $this->artisan('analytics:rollup-daily', ['--date' => '2026-10-03'])->assertSuccessful();

        $this->assertDatabaseMissing('dash_rollup_daily', ['date' => '2026-10-03']);
    }

    public function test_replay_rate_counts_hosts_with_multiple_games(): void
    {
        $date = '2026-10-04';
        // Host 1 plays 2 games, host 2 plays 1 game → 1 of 2 unique hosts = replay rate 0.5
        $this->seedSession($date, 'completed', 1);
        $this->seedSession($date, 'completed', 1);
        $this->seedSession($date, 'completed', 2);

        $this->artisan('analytics:rollup-daily', ['--date' => $date])->assertSuccessful();

        $row = DashRollupDaily::where('date', $date)->firstOrFail();
        $this->assertEqualsWithDelta(0.5, (float) $row->replay_rate, 0.0001);
    }
}
