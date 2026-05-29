<?php

namespace App\Http\Controllers\DashboardWildhunt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WildhuntQuestController extends Controller
{
    private function db() { return DB::connection('wildhunt'); }

    public function index()
    {
        $quests = $this->db()->table('quests')
            ->orderBy('recommended_level')
            ->orderBy('title')
            ->get();

        return view('dashboard.wildhunt.quests.index', compact('quests'));
    }

    public function create()
    {
        return view('dashboard.wildhunt.quests.form', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateQuest($request);
        if ($validated instanceof \Illuminate\Http\RedirectResponse) return $validated;

        $this->db()->table('quests')->insert([
            ...$this->buildRow($validated),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard.wildhunt.quests.index')
            ->with('success', "Quest \"{$validated['title']}\" created.");
    }

    public function edit(int $quest)
    {
        $quest = $this->db()->table('quests')->find($quest);
        abort_if(!$quest, 404);

        return view('dashboard.wildhunt.quests.form', array_merge(
            $this->formData(),
            ['quest' => $quest]
        ));
    }

    public function update(Request $request, int $quest)
    {
        $record = $this->db()->table('quests')->find($quest);
        abort_if(!$record, 404);

        $validated = $this->validateQuest($request);
        if ($validated instanceof \Illuminate\Http\RedirectResponse) return $validated;

        $this->db()->table('quests')->where('id', $quest)->update([
            ...$this->buildRow($validated),
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard.wildhunt.quests.index')
            ->with('success', "Quest \"{$validated['title']}\" updated.");
    }

    public function destroy(int $quest)
    {
        $record = $this->db()->table('quests')->find($quest);
        abort_if(!$record, 404);

        $this->db()->table('quests')->where('id', $quest)->delete();

        return redirect()->route('dashboard.wildhunt.quests.index')
            ->with('success', 'Quest deleted.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function formData(): array
    {
        $monsters = $this->db()->table('monsters')
            ->leftJoin('hunt_quarries', 'monsters.hunt_quarry_id', '=', 'hunt_quarries.id')
            ->select('monsters.id', 'monsters.name', 'monsters.icon', 'monsters.level',
                     'monsters.is_boss', 'hunt_quarries.title as quarry_title')
            ->orderBy('monsters.level')
            ->orderBy('monsters.name')
            ->get();

        $gear = $this->db()->table('gear')
            ->orderBy('name')->get(['id', 'name', 'slot']);

        $pets = $this->db()->table('pet_definitions')
            ->orderBy('name')->get(['slug', 'name', 'icon']);

        return compact('monsters', 'gear', 'pets');
    }

    private function validateQuest(Request $request)
    {
        $validated = $request->validate([
            'title'             => ['required', 'string', 'max:150'],
            'description'       => ['nullable', 'string'],
            'intro_text'        => ['nullable', 'string'],
            'recommended_level' => ['required', 'integer', 'min:1', 'max:12'],
            'waves'             => ['required', 'string'],
            'reward_crests'     => ['required', 'integer', 'min:0'],
            'reward_xp'         => ['required', 'integer', 'min:0'],
            'reward_gear_id'    => ['nullable', 'integer'],
            'reward_pet_slug'   => ['nullable', 'string', 'max:64'],
        ]);

        $decoded = json_decode($validated['waves'], true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded) || empty($decoded)) {
            return back()->withErrors(['waves' => 'Waves must be a valid non-empty JSON array.'])->withInput();
        }

        return $validated;
    }

    private function buildRow(array $v): array
    {
        return [
            'title'             => $v['title'],
            'description'       => $v['description'] ?? null,
            'intro_text'        => $v['intro_text'] ?? null,
            'recommended_level' => $v['recommended_level'],
            'waves'             => $v['waves'],
            'reward_crests'     => $v['reward_crests'],
            'reward_xp'         => $v['reward_xp'],
            'reward_gear_id'    => $v['reward_gear_id'] ?: null,
            'reward_pet_slug'   => $v['reward_pet_slug'] ?: null,
        ];
    }
}
