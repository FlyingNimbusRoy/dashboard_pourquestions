<?php

namespace App\Console\Commands;

use App\Models\Minigame;
use App\Models\Modifier;
use App\Models\ModifierSetting;
use App\Services\Crud\CrudService;
use App\Services\Crud\MinigameService;
use App\Services\Crud\ModifierSettingService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

/**
 * Upserts the socket-server registry (extracted into database/data/socket-registry.json) into the
 * dash_ tables. Only inserts missing keys and fills empty descriptions / settings keys; team edits stay.
 */
class SyncSocketRegistry extends Command
{
    protected $signature = 'dashboard:sync-socket-registry';

    protected $description = 'Insert missing socket minigames/modifiers from database/data/socket-registry.json';

    public function handle(MinigameService $minigames, ModifierSettingService $modifiers): int
    {
        $file = config('dashboard.sockets.registry_file');
        $registry = json_decode((string) @file_get_contents($file), true);
        if (! is_array($registry)) {
            $this->error("Cannot read registry file {$file}");

            return self::FAILURE;
        }

        $created = ['minigames' => 0, 'modifiers' => 0];
        $filled = 0;

        foreach ($registry['minigames'] ?? [] as $row) {
            $existing = Minigame::query()->where('key', $row['key'])->first();
            if (! $existing) {
                $minigames->create($row + ['is_active' => true]);
                $created['minigames']++;

                continue;
            }
            $filled += $this->fill($minigames, $existing, $row);
        }

        $gameMods = Modifier::query()->get(['id', 'name'])->keyBy(fn ($m) => $this->norm($m->name));
        $unmatched = [];

        foreach ($registry['modifiers'] ?? [] as $row) {
            $match = $gameMods->get($this->norm($row['name']));
            $existing = ModifierSetting::query()->where('key', $row['key'])->first();
            if (! $existing) {
                $existing = $modifiers->create($row + ['is_active' => true, 'modifier_id' => $match?->id]);
                $created['modifiers']++;
            } else {
                $filled += $this->fill($modifiers, $existing, $row);
                if ($existing->modifier_id === null && $match) {
                    $modifiers->update($existing, ['modifier_id' => $match->id]);
                }
            }
            if ($existing->refresh()->modifier_id === null) {
                $unmatched[] = $row['key'];
            }
        }

        $this->info("Minigames: {$created['minigames']} created, total ".Minigame::count());
        $this->info("Modifiers: {$created['modifiers']} created, total ".ModifierSetting::count());
        $this->info("Existing rows topped up (empty description/settings keys): {$filled}");
        $this->line('Socket modifiers without a game row ('.count($unmatched).'): '.implode(', ', $unmatched));
        $unusedGame = $gameMods->reject(fn ($m) => ModifierSetting::query()->where('modifier_id', $m->id)->exists())->pluck('name');
        $this->line('Game modifier rows without socket key ('.$unusedGame->count().'): '.$unusedGame->implode(', '));

        return self::SUCCESS;
    }

    private function norm(string $s): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($s));
    }

    /** @param CrudService<Model> $service */
    private function fill(CrudService $service, Model $model, array $row): int
    {
        $update = [];
        if (blank($model->description) && filled($row['description'] ?? null)) {
            $update['description'] = $row['description'];
        }
        $settings = $model->settings ?? [];
        $merged = $settings + ($row['settings'] ?? []);
        if ($merged !== $settings) {
            $update['settings'] = $merged;
        }
        if ($update) {
            $service->update($model, $update);

            return 1;
        }

        return 0;
    }
}
