<?php

namespace App\Services\Socket;

use App\Models\GamepackPurchase;
use App\Models\Minigame;
use App\Models\ModifierSetting;
use App\Models\User;

/**
 * Builds the extension of the socket-server setup payload for a host (plan B in ai/DOCS/sockets-integration.md):
 * allowed minigames, per-item settings and disabled modifiers. Ownership model: config dashboard.sockets.ownership.
 */
class SetupConfigResolver
{
    /**
     * @return array{allowedMinigames: list<string>, minigameSettings: array<string, array<string, mixed>>, modifierSettings: array<string, array<string, mixed>>, disabledModifiers: list<string>}
     */
    public function for(User $host): array
    {
        $owned = $this->ownedPackIds($host);
        $unlocked = fn (?int $packId): bool => $packId === null || in_array($packId, $owned, true);

        $minigames = Minigame::query()->orderBy('sort')->orderBy('key')->get();
        $modifiers = ModifierSetting::query()->orderBy('key')->get();

        $allowed = $minigames->filter(fn (Minigame $m) => $m->is_active && $unlocked($m->gamepack_id));
        $disabled = $modifiers->reject(fn (ModifierSetting $m) => $m->is_active && $unlocked($m->gamepack_id));

        return [
            'allowedMinigames' => $allowed->pluck('key')->values()->all(),
            'minigameSettings' => $allowed->mapWithKeys(fn (Minigame $m) => [$m->key => [
                'minPlayers' => $m->min_players,
                'maxPlayers' => $m->max_players,
                'allowedInRound1' => (bool) ($m->settings['allowed_in_round_1'] ?? true),
                'fixedRound' => $m->settings['fixed_round'] ?? null,
                'weight' => (int) ($m->settings['weight'] ?? 5),
            ]])->all(),
            'modifierSettings' => $modifiers->mapWithKeys(fn (ModifierSetting $m) => [$m->key => array_filter([
                'duration' => $m->settings['duration'] ?? null,
                'shotMultiplier' => $m->settings['shot_multiplier'] ?? null,
                'extraShots' => $m->settings['extra_shots'] ?? null,
            ], fn ($v) => $v !== null)])->all(),
            'disabledModifiers' => $disabled->pluck('key')->values()->all(),
        ];
    }

    /** @return list<int> Gamepack ids that count as unlocked for this host under the configured ownership model. */
    private function ownedPackIds(User $host): array
    {
        // 'host' (default): only the host's purchases matter. Other models need the room's players; not decided yet.
        return GamepackPurchase::query()->where('user_id', $host->id)->pluck('gamepack_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
    }
}
