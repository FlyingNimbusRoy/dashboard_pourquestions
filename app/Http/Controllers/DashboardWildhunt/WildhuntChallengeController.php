<?php

namespace App\Http\Controllers\DashboardWildhunt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WildhuntChallengeController extends Controller
{
    private function db() { return DB::connection('wildhunt'); }

    private const OBJECTIVE_TYPES = [
        'complete_gauntlet' => 'Complete Gauntlets',
        'complete_hunt'     => 'Complete Hunts',
        'slay_boss'         => 'Slay Boss',
        'earn_crests'       => 'Earn Crests',
        'complete_quest'    => 'Complete Quest',
    ];

    public function index(Request $request)
    {
        $query = $this->db()->table('challenges');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('period')) {
            $now = now();
            if ($request->period === 'active') {
                $query->where('active_from', '<=', $now)->where('active_until', '>=', $now);
            } elseif ($request->period === 'upcoming') {
                $query->where('active_from', '>', $now);
            } elseif ($request->period === 'expired') {
                $query->where('active_until', '<', $now);
            }
        }

        $challenges    = $query->orderBy('active_from', 'desc')->paginate(40);
        $challenges->appends($request->only('type', 'period'));
        $objectiveTypes = self::OBJECTIVE_TYPES;

        return view('dashboard.wildhunt.challenges.index', compact('challenges', 'objectiveTypes'));
    }

    public function create()
    {
        return view('dashboard.wildhunt.challenges.form', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateChallenge($request);
        if ($validated instanceof \Illuminate\Http\RedirectResponse) return $validated;

        $this->db()->table('challenges')->insert([
            ...$this->buildRow($validated),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard.wildhunt.challenges.index')
            ->with('success', "Challenge \"{$validated['title']}\" created.");
    }

    public function edit(int $challenge)
    {
        $challenge = $this->db()->table('challenges')->find($challenge);
        abort_if(!$challenge, 404);

        return view('dashboard.wildhunt.challenges.form', array_merge(
            $this->formData(),
            ['challenge' => $challenge]
        ));
    }

    public function update(Request $request, int $challenge)
    {
        $record = $this->db()->table('challenges')->find($challenge);
        abort_if(!$record, 404);

        $validated = $this->validateChallenge($request);
        if ($validated instanceof \Illuminate\Http\RedirectResponse) return $validated;

        $this->db()->table('challenges')->where('id', $challenge)->update([
            ...$this->buildRow($validated),
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard.wildhunt.challenges.index')
            ->with('success', "Challenge \"{$validated['title']}\" updated.");
    }

    public function destroy(int $challenge)
    {
        $record = $this->db()->table('challenges')->find($challenge);
        abort_if(!$record, 404);

        $this->db()->table('challenges')->where('id', $challenge)->delete();

        return redirect()->route('dashboard.wildhunt.challenges.index')
            ->with('success', 'Challenge deleted.');
    }

    public function duplicate(int $challenge)
    {
        $record = $this->db()->table('challenges')->find($challenge);
        abort_if(!$record, 404);

        $this->db()->table('challenges')->insert([
            'type'             => $record->type,
            'title'            => $record->title . ' (copy)',
            'description'      => $record->description,
            'icon'             => $record->icon,
            'objective_type'   => $record->objective_type,
            'objective_config' => $record->objective_config,
            'objective_target' => $record->objective_target,
            'reward_crests'    => $record->reward_crests,
            'reward_xp'        => $record->reward_xp,
            'reward_gear_id'   => $record->reward_gear_id,
            'reward_pet_slug'  => $record->reward_pet_slug,
            // Shift active window forward by the challenge's natural duration
            'active_from'  => now(),
            'active_until' => now()->addDay(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('dashboard.wildhunt.challenges.index')
            ->with('success', 'Challenge duplicated — update the active window before publishing.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function formData(): array
    {
        $bosses  = $this->db()->table('monsters')
            ->where('is_boss', true)
            ->leftJoin('hunt_quarries', 'monsters.hunt_quarry_id', '=', 'hunt_quarries.id')
            ->select('monsters.id', 'monsters.name', 'monsters.icon', 'hunt_quarries.title as quarry_title')
            ->orderBy('monsters.name')->get();

        $quarries = $this->db()->table('hunt_quarries')
            ->orderBy('tier')->orderBy('title')->get();

        $quests  = $this->db()->table('quests')
            ->orderBy('title')->get(['id', 'title', 'recommended_level']);

        $gear    = $this->db()->table('gear')
            ->orderBy('name')->get(['id', 'name', 'slot']);

        $pets    = $this->db()->table('pet_definitions')
            ->orderBy('name')->get(['slug', 'name', 'icon']);

        return compact('bosses', 'quarries', 'quests', 'gear', 'pets') + [
            'objectiveTypes' => self::OBJECTIVE_TYPES,
        ];
    }

    private function validateChallenge(Request $request)
    {
        $validated = $request->validate([
            'type'             => ['required', 'string', 'in:daily,weekly,monthly'],
            'title'            => ['required', 'string', 'max:150'],
            'description'      => ['nullable', 'string'],
            'icon'             => ['nullable', 'string', 'max:10'],
            'objective_type'   => ['required', 'string', 'in:' . implode(',', array_keys(self::OBJECTIVE_TYPES))],
            'objective_config' => ['required', 'string'],
            'objective_target' => ['required', 'integer', 'min:1'],
            'reward_crests'    => ['required', 'integer', 'min:0'],
            'reward_xp'        => ['required', 'integer', 'min:0'],
            'reward_gear_id'   => ['nullable', 'integer'],
            'reward_pet_slug'  => ['nullable', 'string', 'max:64'],
            'active_from'      => ['required', 'date'],
            'active_until'     => ['required', 'date', 'after:active_from'],
        ]);

        json_decode($validated['objective_config']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['objective_config' => 'Invalid JSON in objective config.'])->withInput();
        }

        return $validated;
    }

    private function buildRow(array $v): array
    {
        return [
            'type'             => $v['type'],
            'title'            => $v['title'],
            'description'      => $v['description'] ?? null,
            'icon'             => $v['icon'] ?? null,
            'objective_type'   => $v['objective_type'],
            'objective_config' => $v['objective_config'],
            'objective_target' => $v['objective_target'],
            'reward_crests'    => $v['reward_crests'],
            'reward_xp'        => $v['reward_xp'],
            'reward_gear_id'   => $v['reward_gear_id'] ?: null,
            'reward_pet_slug'  => $v['reward_pet_slug'] ?: null,
            'active_from'      => $v['active_from'],
            'active_until'     => $v['active_until'],
        ];
    }
}
