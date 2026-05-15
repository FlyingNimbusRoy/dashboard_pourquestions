<?php

namespace App\Http\Controllers\DashboardWildhunt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WildhuntMonsterController extends Controller
{
    private function db() { return DB::connection('wildhunt'); }

    public function index(Request $request)
    {
        $query = $this->db()->table('monsters')
            ->leftJoin('hunt_quarries', 'monsters.hunt_quarry_id', '=', 'hunt_quarries.id')
            ->select('monsters.*', 'hunt_quarries.title as quarry_title', 'hunt_quarries.tier as quarry_tier');

        if ($request->filled('search')) {
            $query->where('monsters.name', 'like', "%{$request->search}%");
        }
        if ($request->filled('quarry_id')) {
            $query->where('monsters.hunt_quarry_id', $request->quarry_id);
        }
        if ($request->filled('is_boss')) {
            $query->where('monsters.is_boss', $request->is_boss);
        }

        $monsters  = $query->orderBy('hunt_quarries.tier')->orderBy('monsters.level')->paginate(40);
        $monsters->appends($request->only('search', 'quarry_id', 'is_boss'));

        $quarries = $this->db()->table('hunt_quarries')->orderBy('tier')->orderBy('title')->get();

        return view('dashboard.wildhunt.monsters.index', compact('monsters', 'quarries'));
    }

    public function create(Request $request)
    {
        $quarries       = $this->db()->table('hunt_quarries')->orderBy('tier')->orderBy('title')->get();
        $selectedQuarry = $request->query('quarry_id');
        return view('dashboard.wildhunt.monsters.form', compact('quarries', 'selectedQuarry'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'icon'             => ['required', 'string', 'max:10'],
            'hunt_quarry_id'   => ['required', 'integer'],
            'level'            => ['required', 'integer', 'min:1'],
            'hp'               => ['required', 'integer', 'min:1'],
            'defense'          => ['required', 'integer', 'min:0'],
            'is_boss'          => ['boolean'],
            'exp_reward'       => ['required', 'integer', 'min:0'],
            'crest_min'        => ['required', 'integer', 'min:0'],
            'crest_max'        => ['required', 'integer', 'min:0'],
            'personality'      => ['nullable', 'string'],
            'resistances'      => ['nullable', 'string'],
            'vulnerabilities'  => ['nullable', 'string'],
            'moves'            => ['required', 'string'],
        ]);

        // Validate JSON fields
        foreach (['resistances', 'vulnerabilities', 'moves', 'personality'] as $field) {
            if (!empty($validated[$field])) {
                json_decode($validated[$field]);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->withErrors([$field => "Invalid JSON in {$field}."])->withInput();
                }
            }
        }

        $this->db()->table('monsters')->insert([
            'name'            => $validated['name'],
            'icon'            => $validated['icon'],
            'hunt_quarry_id'  => $validated['hunt_quarry_id'],
            'level'           => $validated['level'],
            'hp'              => $validated['hp'],
            'defense'         => $validated['defense'],
            'is_boss'         => $request->boolean('is_boss'),
            'exp_reward'      => $validated['exp_reward'],
            'crest_min'       => $validated['crest_min'],
            'crest_max'       => $validated['crest_max'],
            'personality'     => $validated['personality'] ?? null,
            'resistances'     => $validated['resistances'] ?? null,
            'vulnerabilities' => $validated['vulnerabilities'] ?? null,
            'moves'           => $validated['moves'],
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()->route('dashboard.wildhunt.monsters.index')
            ->with('success', "Monster \"{$validated['name']}\" created.");
    }

    public function edit(int $monster)
    {
        $monster  = $this->db()->table('monsters')->find($monster);
        abort_if(!$monster, 404);

        $quarries = $this->db()->table('hunt_quarries')->orderBy('tier')->orderBy('title')->get();

        return view('dashboard.wildhunt.monsters.form', compact('monster', 'quarries'));
    }

    public function update(Request $request, int $monster)
    {
        $record = $this->db()->table('monsters')->find($monster);
        abort_if(!$record, 404);

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'icon'             => ['required', 'string', 'max:10'],
            'hunt_quarry_id'   => ['required', 'integer'],
            'level'            => ['required', 'integer', 'min:1'],
            'hp'               => ['required', 'integer', 'min:1'],
            'defense'          => ['required', 'integer', 'min:0'],
            'is_boss'          => ['boolean'],
            'exp_reward'       => ['required', 'integer', 'min:0'],
            'crest_min'        => ['required', 'integer', 'min:0'],
            'crest_max'        => ['required', 'integer', 'min:0'],
            'personality'      => ['nullable', 'string'],
            'resistances'      => ['nullable', 'string'],
            'vulnerabilities'  => ['nullable', 'string'],
            'moves'            => ['required', 'string'],
        ]);

        foreach (['resistances', 'vulnerabilities', 'moves', 'personality'] as $field) {
            if (!empty($validated[$field])) {
                json_decode($validated[$field]);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->withErrors([$field => "Invalid JSON in {$field}."])->withInput();
                }
            }
        }

        $this->db()->table('monsters')->where('id', $monster)->update([
            'name'            => $validated['name'],
            'icon'            => $validated['icon'],
            'hunt_quarry_id'  => $validated['hunt_quarry_id'],
            'level'           => $validated['level'],
            'hp'              => $validated['hp'],
            'defense'         => $validated['defense'],
            'is_boss'         => $request->boolean('is_boss'),
            'exp_reward'      => $validated['exp_reward'],
            'crest_min'       => $validated['crest_min'],
            'crest_max'       => $validated['crest_max'],
            'personality'     => $validated['personality'] ?? null,
            'resistances'     => $validated['resistances'] ?? null,
            'vulnerabilities' => $validated['vulnerabilities'] ?? null,
            'moves'           => $validated['moves'],
            'updated_at'      => now(),
        ]);

        return redirect()->route('dashboard.wildhunt.monsters.index')
            ->with('success', "Monster \"{$validated['name']}\" updated.");
    }

    public function destroy(int $monster)
    {
        $record = $this->db()->table('monsters')->find($monster);
        abort_if(!$record, 404);

        $this->db()->table('monsters')->where('id', $monster)->delete();

        return redirect()->route('dashboard.wildhunt.monsters.index')
            ->with('success', 'Monster deleted.');
    }
}
