<?php

namespace Tests\Feature;

use App\Models\DashLiveSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private function post(array $payload): \Illuminate\Testing\TestResponse
    {
        config(['dashboard.ingest.secret' => 'test-secret']);
        return $this->withHeaders(['X-Ingest-Secret' => 'test-secret'])
                    ->postJson('/internal/live-snapshot', $payload);
    }

    public function test_snapshot_without_secret_is_rejected(): void
    {
        $this->postJson('/internal/live-snapshot', ['open_rooms' => 5, 'players_online' => 20])
             ->assertStatus(401);
    }

    public function test_snapshot_is_stored_with_correct_fields(): void
    {
        $this->post([
            'open_rooms'      => 12,
            'players_online'  => 48,
            'games_today'     => 7,
            'rooms_per_phase' => ['lobby' => 3, 'playing' => 9],
        ])->assertStatus(201)->assertJson(['status' => 'ok']);

        $snap = DashLiveSnapshot::first();
        $this->assertSame(12, $snap->open_rooms);
        $this->assertSame(48, $snap->players_online);
        $this->assertSame(7, $snap->games_today);
        $this->assertSame(['lobby' => 3, 'playing' => 9], $snap->rooms_per_phase);
    }

    public function test_failed_post_count_is_clamped_to_255(): void
    {
        $this->post([
            'open_rooms'        => 0,
            'players_online'    => 0,
            'failed_post_count' => 999,
        ])->assertStatus(201);

        $this->assertSame(255, (int) DashLiveSnapshot::value('failed_post_count'));
    }

    public function test_optional_fields_default_correctly(): void
    {
        $this->post(['open_rooms' => 2, 'players_online' => 6])->assertStatus(201);

        $snap = DashLiveSnapshot::first();
        $this->assertSame(0, $snap->games_today);
        $this->assertNull($snap->rooms_per_phase);
        $this->assertSame(0, (int) $snap->failed_post_count);
        $this->assertSame(0, (int) $snap->retry_queue_size);
    }
}
