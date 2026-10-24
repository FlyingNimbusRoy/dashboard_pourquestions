<?php

namespace Tests\Feature;

use App\Models\Gamepack;
use App\Models\GamepackPurchase;
use App\Models\Minigame;
use App\Models\ModifierSetting;
use App\Models\User;
use App\Services\Socket\SetupConfigResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupConfigResolverTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private SetupConfigResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->resolver = app(SetupConfigResolver::class);
    }

    private function minigame(array $attrs): Minigame
    {
        return Minigame::create(array_merge([
            'key'         => 'test_game',
            'name'        => 'Test Game',
            'is_active'   => true,
            'min_players' => 2,
            'max_players' => 8,
            'settings'    => [],
            'sort'        => 0,
        ], $attrs));
    }

    private function modifier(array $attrs): ModifierSetting
    {
        return ModifierSetting::create(array_merge([
            'key'      => 'test_mod',
            'name'     => 'Test Modifier',
            'is_active' => true,
            'settings' => [],
        ], $attrs));
    }

    public function test_free_minigame_is_allowed_for_any_user(): void
    {
        $this->minigame(['key' => 'quiz_battle', 'gamepack_id' => null]);

        $result = $this->resolver->for($this->user);

        $this->assertContains('quiz_battle', $result['allowedMinigames']);
        $this->assertArrayHasKey('quiz_battle', $result['minigameSettings']);
    }

    public function test_inactive_minigame_is_excluded(): void
    {
        $this->minigame(['key' => 'inactive_game', 'is_active' => false, 'gamepack_id' => null]);

        $result = $this->resolver->for($this->user);

        $this->assertNotContains('inactive_game', $result['allowedMinigames']);
        $this->assertArrayNotHasKey('inactive_game', $result['minigameSettings']);
    }

    public function test_locked_minigame_excluded_without_purchase(): void
    {
        $pack = Gamepack::factory()->create();
        $this->minigame(['key' => 'exclusive_game', 'gamepack_id' => $pack->id]);

        $result = $this->resolver->for($this->user);

        $this->assertNotContains('exclusive_game', $result['allowedMinigames']);
    }

    public function test_owned_pack_unlocks_minigame(): void
    {
        $pack = Gamepack::factory()->create();
        $this->minigame(['key' => 'pack_game', 'gamepack_id' => $pack->id]);
        GamepackPurchase::factory()->create(['user_id' => $this->user->id, 'gamepack_id' => $pack->id]);

        $result = $this->resolver->for($this->user);

        $this->assertContains('pack_game', $result['allowedMinigames']);
        $this->assertArrayHasKey('pack_game', $result['minigameSettings']);
    }

    public function test_modifier_settings_include_all_keys_and_inactive_appear_in_disabled(): void
    {
        $this->modifier(['key' => 'active_mod',   'is_active' => true,  'settings' => ['duration' => 30]]);
        $this->modifier(['key' => 'inactive_mod', 'is_active' => false, 'settings' => ['duration' => 60]]);

        $result = $this->resolver->for($this->user);

        $this->assertArrayHasKey('active_mod',   $result['modifierSettings']);
        $this->assertArrayHasKey('inactive_mod', $result['modifierSettings']);
        $this->assertNotContains('active_mod',   $result['disabledModifiers']);
        $this->assertContains('inactive_mod',    $result['disabledModifiers']);
    }

    public function test_locked_modifier_without_purchase_appears_in_disabled(): void
    {
        $pack = Gamepack::factory()->create();
        $this->modifier(['key' => 'locked_mod', 'is_active' => true, 'gamepack_id' => $pack->id]);

        $result = $this->resolver->for($this->user);

        $this->assertContains('locked_mod', $result['disabledModifiers']);
    }

    public function test_minigame_settings_payload_shape(): void
    {
        $this->minigame([
            'key'         => 'shape_game',
            'min_players' => 3,
            'max_players' => 10,
            'settings'    => ['weight' => 7, 'fixed_round' => 2, 'allowed_in_round_1' => false],
        ]);

        $result = $this->resolver->for($this->user);

        $s = $result['minigameSettings']['shape_game'];
        $this->assertSame(3, $s['minPlayers']);
        $this->assertSame(10, $s['maxPlayers']);
        $this->assertFalse($s['allowedInRound1']);
        $this->assertSame(2, $s['fixedRound']);
        $this->assertSame(7, $s['weight']);
    }
}
