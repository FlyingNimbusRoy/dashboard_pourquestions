<?php

namespace App\Http\Controllers\DashboardWildhunt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WildhuntQuarryController extends Controller
{
    private function db() { return DB::connection('wildhunt'); }

    public function index()
    {
        $quarries = $this->db()
            ->table('hunt_quarries')
            ->orderBy('tier')
            ->orderBy('title')
            ->get();

        $quarries = $quarries->map(function ($q) {
            $q->monster_count = $this->db()->table('monsters')
                ->where('hunt_quarry_id', $q->id)->count();
            return $q;
        });

        return view('dashboard.wildhunt.quarries.index', compact('quarries'));
    }

    public function create()
    {
        [$bosses, $pets, $gear] = $this->dropdownData();
        return view('dashboard.wildhunt.quarries.form', compact('bosses', 'pets', 'gear'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateQuarry($request);
        if ($validated instanceof \Illuminate\Http\RedirectResponse) return $validated;

        $this->db()->table('hunt_quarries')->insert([
            ...$this->buildRow($validated, $request),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard.wildhunt.quarries.index')
            ->with('success', "Quarry \"{$validated['title']}\" created.");
    }

    public function edit(int $quarry)
    {
        $quarry = $this->db()->table('hunt_quarries')->find($quarry);
        abort_if(!$quarry, 404);

        $monsters = $this->db()->table('monsters')
            ->where('hunt_quarry_id', $quarry->id)
            ->orderBy('is_boss', 'desc')
            ->orderBy('level')
            ->get();

        [$bosses, $pets, $gear] = $this->dropdownData();

        return view('dashboard.wildhunt.quarries.form', compact('quarry', 'monsters', 'bosses', 'pets', 'gear'));
    }

    public function update(Request $request, int $quarry)
    {
        $record = $this->db()->table('hunt_quarries')->find($quarry);
        abort_if(!$record, 404);

        $validated = $this->validateQuarry($request);
        if ($validated instanceof \Illuminate\Http\RedirectResponse) return $validated;

        $this->db()->table('hunt_quarries')->where('id', $quarry)->update([
            ...$this->buildRow($validated, $request),
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard.wildhunt.quarries.index')
            ->with('success', "Quarry \"{$validated['title']}\" updated.");
    }

    public function destroy(int $quarry)
    {
        $record = $this->db()->table('hunt_quarries')->find($quarry);
        abort_if(!$record, 404);

        $this->db()->table('hunt_quarries')->where('id', $quarry)->delete();

        return redirect()->route('dashboard.wildhunt.quarries.index')
            ->with('success', 'Quarry deleted.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function dropdownData(): array
    {
        $bosses = $this->db()->table('monsters')
            ->where('is_boss', true)
            ->leftJoin('hunt_quarries', 'monsters.hunt_quarry_id', '=', 'hunt_quarries.id')
            ->select('monsters.id', 'monsters.name', 'monsters.icon', 'hunt_quarries.title as quarry_title')
            ->orderBy('hunt_quarries.tier')
            ->orderBy('monsters.name')
            ->get();

        $pets = $this->db()->table('pet_definitions')
            ->orderBy('name')
            ->get(['id', 'slug', 'name', 'icon']);

        $gear = $this->db()->table('gear')
            ->orderBy('name')
            ->get(['id', 'name', 'slot', 'is_gauntlet_loot']);

        return [$bosses, $pets, $gear];
    }

    private function validateQuarry(Request $request)
    {
        return $request->validate([
            'type'             => ['required', 'string', 'max:100'],
            'title'            => ['required', 'string', 'max:150'],
            'description'      => ['required', 'string'],
            'tier'             => ['required', 'integer', 'min:1', 'max:6'],
            'min_player_level' => ['required', 'integer', 'min:1'],
            'multiplayer_only' => ['boolean'],
            'boss_monster_id'  => ['nullable', 'integer'],
            'boss_monster_id_2'=> ['nullable', 'integer'],
            'loot_pet_slug'    => ['nullable', 'string', 'max:64'],
            'loot_gear_id'     => ['nullable', 'integer'],
        ]);
    }

    private function buildRow(array $validated, Request $request): array
    {
        return [
            'type'              => $validated['type'],
            'title'             => $validated['title'],
            'description'       => $validated['description'],
            'tier'              => $validated['tier'],
            'min_player_level'  => $validated['min_player_level'],
            'multiplayer_only'  => $request->boolean('multiplayer_only'),
            'boss_monster_id'   => $validated['boss_monster_id']   ?: null,
            'boss_monster_id_2' => $validated['boss_monster_id_2'] ?: null,
            'loot_pet_slug'     => $validated['loot_pet_slug']      ?: null,
            'loot_gear_id'      => $validated['loot_gear_id']       ?: null,
        ];
    }
}
