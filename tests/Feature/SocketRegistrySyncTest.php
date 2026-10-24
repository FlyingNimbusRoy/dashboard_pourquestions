<?php

namespace Tests\Feature;

use App\Models\Minigame;
use App\Models\Modifier;
use App\Models\ModifierSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocketRegistrySyncTest extends TestCase
{
    use RefreshDatabase;

    private string $tmpFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'pq_registry_') . '.json';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
        parent::tearDown();
    }

    private function setRegistry(array $payload): void
    {
        file_put_contents($this->tmpFile, json_encode($payload));
        config(['dashboard.sockets.registry_file' => $this->tmpFile]);
    }

    public function test_sync_inserts_new_minigames_and_modifiers(): void
    {
        $this->setRegistry([
            'minigames' => [
                ['key' => 'dice', 'name' => 'Dice', 'description' => 'Roll dice.', 'sort' => 1,
                 'min_players' => null, 'max_players' => null,
                 'settings' => ['weight' => 5, 'allowed_in_round_1' => true, 'fixed_round' => null]],
            ],
            'modifiers' => [
                ['key' => 'HIGH_TOLERANCE', 'name' => 'High Tolerance', 'description' => 'Drink more.',
                 'turnbased' => true, 'settings' => ['duration' => 3]],
            ],
        ]);

        $this->artisan('dashboard:sync-socket-registry')->assertSuccessful();

        $this->assertDatabaseHas('dash_minigames', ['key' => 'dice', 'name' => 'Dice']);
        $this->assertDatabaseHas('dash_modifier_settings', ['key' => 'HIGH_TOLERANCE', 'name' => 'High Tolerance']);
        $this->assertSame(
            ['weight' => 5, 'allowed_in_round_1' => true, 'fixed_round' => null],
            Minigame::first()->settings
        );
        $this->assertSame(['duration' => 3], ModifierSetting::first()->settings);
    }

    public function test_sync_is_idempotent(): void
    {
        $this->setRegistry([
            'minigames' => [
                ['key' => 'dice', 'name' => 'Dice', 'description' => 'Roll.', 'sort' => 1,
                 'min_players' => null, 'max_players' => null, 'settings' => ['weight' => 5]],
            ],
            'modifiers' => [],
        ]);

        $this->artisan('dashboard:sync-socket-registry')->assertSuccessful();
        $this->artisan('dashboard:sync-socket-registry')->assertSuccessful();

        $this->assertDatabaseCount('dash_minigames', 1);
    }

    public function test_sync_fills_empty_description_without_overwriting_team_edits(): void
    {
        $this->setRegistry([
            'minigames' => [
                ['key' => 'dice', 'name' => 'Dice', 'description' => 'From registry.',
                 'sort' => 1, 'min_players' => null, 'max_players' => null, 'settings' => []],
            ],
            'modifiers' => [],
        ]);

        // Pre-existing row with no description yet
        Minigame::create(['key' => 'dice', 'name' => 'Dice', 'is_active' => true]);

        $this->artisan('dashboard:sync-socket-registry')->assertSuccessful();

        $this->assertDatabaseHas('dash_minigames', ['key' => 'dice', 'description' => 'From registry.']);
        $this->assertDatabaseCount('dash_minigames', 1);

        // A second run must not wipe the description (it's now non-empty, fill skips it)
        $this->artisan('dashboard:sync-socket-registry')->assertSuccessful();
        $this->assertDatabaseCount('dash_minigames', 1);
    }

    public function test_sync_links_modifier_setting_to_game_modifier_by_name(): void
    {
        $gameModifier = Modifier::factory()->create(['name' => 'High Tolerance']);

        $this->setRegistry([
            'minigames' => [],
            'modifiers' => [
                ['key' => 'HIGH_TOLERANCE', 'name' => 'High Tolerance', 'description' => null,
                 'turnbased' => true, 'settings' => ['duration' => 3]],
            ],
        ]);

        $this->artisan('dashboard:sync-socket-registry')->assertSuccessful();

        $setting = ModifierSetting::where('key', 'HIGH_TOLERANCE')->firstOrFail();
        $this->assertSame($gameModifier->id, $setting->modifier_id);
    }

    public function test_sync_fails_gracefully_with_missing_registry_file(): void
    {
        config(['dashboard.sockets.registry_file' => '/nonexistent/path/registry.json']);

        $this->artisan('dashboard:sync-socket-registry')->assertFailed();
    }
}
