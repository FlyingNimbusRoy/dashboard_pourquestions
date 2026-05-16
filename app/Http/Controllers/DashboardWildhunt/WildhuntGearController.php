<?php

namespace App\Http\Controllers\DashboardWildhunt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WildhuntGearController extends Controller
{
    private function db() { return DB::connection('wildhunt'); }

    public function index(Request $request)
    {
        $query = $this->db()->table('gear')
            ->leftJoin('classes', 'gear.class_id', '=', 'classes.id')
            ->leftJoin('hunt_quarries', 'gear.gauntlet_quarry_id', '=', 'hunt_quarries.id')
            ->select(
                'gear.*',
                'classes.name as class_name',
                'hunt_quarries.title as quarry_title'
            );

        if ($request->filled('search')) {
            $query->where('gear.name', 'like', "%{$request->search}%");
        }
        if ($request->filled('slot')) {
            $query->where('gear.slot', $request->slot);
        }
        if ($request->filled('is_gauntlet_loot')) {
            $query->where('gear.is_gauntlet_loot', $request->is_gauntlet_loot);
        }
        if ($request->filled('class_id')) {
            $query->where('gear.class_id', $request->class_id);
        }

        $gear    = $query->orderBy('gear.is_gauntlet_loot', 'desc')->orderBy('gear.slot')->orderBy('gear.name')->paginate(40);
        $gear->appends($request->only('search', 'slot', 'is_gauntlet_loot', 'class_id'));

        $classes  = $this->db()->table('classes')->orderBy('name')->get();
        $quarries = $this->db()->table('hunt_quarries')->orderBy('tier')->orderBy('title')->get();
        $slots    = ['weapon', 'trinket', 'head', 'body'];

        return view('dashboard.wildhunt.gear.index', compact('gear', 'classes', 'quarries', 'slots'));
    }

    public function create()
    {
        $classes     = $this->db()->table('classes')->orderBy('name')->get();
        $quarries    = $this->db()->table('hunt_quarries')->orderBy('tier')->orderBy('title')->get();
        $slots       = ['weapon', 'trinket', 'head', 'body'];
        $damageTypes = ['physical', 'fire', 'poison', 'frost', 'radiant', 'dark'];
        $skills      = $this->db()->table('class_skills')
            ->leftJoin('classes', 'class_skills.class_id', '=', 'classes.id')
            ->select('class_skills.id', 'class_skills.name', 'class_skills.icon', 'classes.name as class_name')
            ->orderBy('classes.name')->orderBy('class_skills.name')
            ->get();

        return view('dashboard.wildhunt.gear.form', compact('classes', 'quarries', 'slots', 'damageTypes', 'skills'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:100'],
            'slot'                 => ['required', 'string'],
            'description'          => ['required', 'string'],
            'class_id'             => ['nullable', 'integer'],
            'crest_cost'           => ['required', 'integer', 'min:0'],
            'hp_bonus'             => ['required', 'integer'],
            'attack_bonus'         => ['required', 'integer'],
            'defense_bonus'        => ['required', 'integer'],
            'intelligence_bonus'   => ['required', 'integer'],
            'stealth_bonus'        => ['required', 'integer'],
            'arcana_bonus'         => ['required', 'integer'],
            'max_actions_bonus'    => ['required', 'integer'],
            'regen'                => ['required', 'integer', 'min:0'],
            'is_gauntlet_loot'     => ['boolean'],
            'gauntlet_tier'        => ['nullable', 'integer', 'min:1', 'max:6'],
            'gauntlet_quarry_id'   => ['nullable', 'integer'],
            'drop_weight'          => ['nullable', 'integer', 'min:1'],
            'resistances'          => ['nullable', 'string'],
            'damage_overrides'     => ['nullable', 'string'],
            'bonus_skill_ids'      => ['nullable', 'string'],
        ]);

        foreach (['resistances', 'damage_overrides', 'bonus_skill_ids'] as $field) {
            if (!empty($validated[$field])) {
                json_decode($validated[$field]);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->withErrors([$field => "Invalid JSON in {$field}."])->withInput();
                }
            }
        }

        $this->db()->table('gear')->insert([
            'name'               => $validated['name'],
            'slot'               => $validated['slot'],
            'description'        => $validated['description'],
            'class_id'           => $validated['class_id'] ?: null,
            'crest_cost'         => $validated['crest_cost'],
            'hp_bonus'           => $validated['hp_bonus'],
            'attack_bonus'       => $validated['attack_bonus'],
            'defense_bonus'      => $validated['defense_bonus'],
            'intelligence_bonus' => $validated['intelligence_bonus'],
            'stealth_bonus'      => $validated['stealth_bonus'],
            'arcana_bonus'       => $validated['arcana_bonus'],
            'max_actions_bonus'  => $validated['max_actions_bonus'],
            'regen'              => $validated['regen'],
            'is_gauntlet_loot'   => $request->boolean('is_gauntlet_loot'),
            'gauntlet_tier'      => $validated['gauntlet_tier'] ?: null,
            'gauntlet_quarry_id' => $validated['gauntlet_quarry_id'] ?: null,
            'drop_weight'        => $validated['drop_weight'] ?? 100,
            'resistances'        => $validated['resistances'] ?: null,
            'damage_overrides'   => $validated['damage_overrides'] ?: null,
            'bonus_skill_ids'    => $validated['bonus_skill_ids'] ?: null,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return redirect()->route('dashboard.wildhunt.gear.index')
            ->with('success', "Gear \"{$validated['name']}\" created.");
    }

    public function edit(int $gear)
    {
        $item     = $this->db()->table('gear')->find($gear);
        abort_if(!$item, 404);

        $classes     = $this->db()->table('classes')->orderBy('name')->get();
        $quarries    = $this->db()->table('hunt_quarries')->orderBy('tier')->orderBy('title')->get();
        $slots       = ['weapon', 'trinket', 'head', 'body'];
        $damageTypes = ['physical', 'fire', 'poison', 'frost', 'radiant', 'dark'];
        $skills      = $this->db()->table('class_skills')
            ->leftJoin('classes', 'class_skills.class_id', '=', 'classes.id')
            ->select('class_skills.id', 'class_skills.name', 'class_skills.icon', 'classes.name as class_name')
            ->orderBy('classes.name')->orderBy('class_skills.name')
            ->get();

        return view('dashboard.wildhunt.gear.form', compact('item', 'classes', 'quarries', 'slots', 'damageTypes', 'skills'));
    }

    public function update(Request $request, int $gear)
    {
        $record = $this->db()->table('gear')->find($gear);
        abort_if(!$record, 404);

        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:100'],
            'slot'                 => ['required', 'string'],
            'description'          => ['required', 'string'],
            'class_id'             => ['nullable', 'integer'],
            'crest_cost'           => ['required', 'integer', 'min:0'],
            'hp_bonus'             => ['required', 'integer'],
            'attack_bonus'         => ['required', 'integer'],
            'defense_bonus'        => ['required', 'integer'],
            'intelligence_bonus'   => ['required', 'integer'],
            'stealth_bonus'        => ['required', 'integer'],
            'arcana_bonus'         => ['required', 'integer'],
            'max_actions_bonus'    => ['required', 'integer'],
            'regen'                => ['required', 'integer', 'min:0'],
            'is_gauntlet_loot'     => ['boolean'],
            'gauntlet_tier'        => ['nullable', 'integer', 'min:1', 'max:6'],
            'gauntlet_quarry_id'   => ['nullable', 'integer'],
            'drop_weight'          => ['nullable', 'integer', 'min:1'],
            'resistances'          => ['nullable', 'string'],
            'damage_overrides'     => ['nullable', 'string'],
            'bonus_skill_ids'      => ['nullable', 'string'],
        ]);

        foreach (['resistances', 'damage_overrides', 'bonus_skill_ids'] as $field) {
            if (!empty($validated[$field])) {
                json_decode($validated[$field]);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->withErrors([$field => "Invalid JSON in {$field}."])->withInput();
                }
            }
        }

        $this->db()->table('gear')->where('id', $gear)->update([
            'name'               => $validated['name'],
            'slot'               => $validated['slot'],
            'description'        => $validated['description'],
            'class_id'           => $validated['class_id'] ?: null,
            'crest_cost'         => $validated['crest_cost'],
            'hp_bonus'           => $validated['hp_bonus'],
            'attack_bonus'       => $validated['attack_bonus'],
            'defense_bonus'      => $validated['defense_bonus'],
            'intelligence_bonus' => $validated['intelligence_bonus'],
            'stealth_bonus'      => $validated['stealth_bonus'],
            'arcana_bonus'       => $validated['arcana_bonus'],
            'max_actions_bonus'  => $validated['max_actions_bonus'],
            'regen'              => $validated['regen'],
            'is_gauntlet_loot'   => $request->boolean('is_gauntlet_loot'),
            'gauntlet_tier'      => $validated['gauntlet_tier'] ?: null,
            'gauntlet_quarry_id' => $validated['gauntlet_quarry_id'] ?: null,
            'drop_weight'        => $validated['drop_weight'] ?? 100,
            'resistances'        => $validated['resistances'] ?: null,
            'damage_overrides'   => $validated['damage_overrides'] ?: null,
            'bonus_skill_ids'    => $validated['bonus_skill_ids'] ?: null,
            'updated_at'         => now(),
        ]);

        return redirect()->route('dashboard.wildhunt.gear.index')
            ->with('success', "Gear \"{$validated['name']}\" updated.");
    }

    public function destroy(int $gear)
    {
        $record = $this->db()->table('gear')->find($gear);
        abort_if(!$record, 404);

        $this->db()->table('gear')->where('id', $gear)->delete();

        return redirect()->route('dashboard.wildhunt.gear.index')
            ->with('success', 'Gear deleted.');
    }
}