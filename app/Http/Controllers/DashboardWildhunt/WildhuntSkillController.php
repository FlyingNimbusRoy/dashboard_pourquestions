<?php

namespace App\Http\Controllers\DashboardWildhunt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WildhuntSkillController extends Controller
{
    private function db() { return DB::connection('wildhunt'); }

    public function index(Request $request)
    {
        $query = $this->db()->table('class_skills')
            ->leftJoin('classes', 'class_skills.class_id', '=', 'classes.id')
            ->select('class_skills.*', 'classes.name as class_name');

        if ($request->filled('search')) {
            $query->where('class_skills.name', 'like', "%{$request->search}%");
        }
        if ($request->filled('class_id')) {
            $query->where('class_skills.class_id', $request->class_id);
        }
        if ($request->filled('type')) {
            $query->where('class_skills.type', $request->type);
        }
        if ($request->filled('damage_type')) {
            $query->where('class_skills.damage_type', $request->damage_type);
        }

        $skills = $query->orderBy('classes.name')->orderBy('class_skills.level_required')->paginate(50);
        $skills->appends($request->only('search', 'class_id', 'type', 'damage_type'));

        $classes     = $this->db()->table('classes')->orderBy('name')->get();
        $types       = ['atk', 'def', 'skill', 'heal', 'boon_atk', 'boon_def'];
        $damageTypes = ['physical', 'fire', 'poison', 'frost', 'radiant', 'dark'];

        return view('dashboard.wildhunt.skills.index', compact('skills', 'classes', 'types', 'damageTypes'));
    }

    public function create()
    {
        $classes     = $this->db()->table('classes')->orderBy('name')->get();
        $types       = ['atk', 'def', 'skill', 'heal', 'boon_atk', 'boon_def'];
        $damageTypes = ['physical', 'fire', 'poison', 'frost', 'radiant', 'dark'];

        return view('dashboard.wildhunt.skills.form', compact('classes', 'types', 'damageTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug'           => ['required', 'string', 'max:100'],
            'name'           => ['required', 'string', 'max:100'],
            'icon'           => ['required', 'string', 'max:10'],
            'class_id'       => ['nullable', 'integer'],
            'type'           => ['required', 'string'],
            'damage_type'    => ['nullable', 'string'],
            'action_cost'    => ['required', 'integer', 'min:0'],
            'value'          => ['required', 'integer', 'min:0'],
            'level_required' => ['required', 'integer', 'min:1'],
            'description'    => ['required', 'string'],
            'extra'          => ['nullable', 'string'],
        ]);

        if (!empty($validated['extra'])) {
            json_decode($validated['extra']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['extra' => 'Invalid JSON in extra.'])->withInput();
            }
        }

        $this->db()->table('class_skills')->insert([
            ...$validated,
            'class_id'   => $validated['class_id'] ?: null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard.wildhunt.skills.index')
            ->with('success', "Skill \"{$validated['name']}\" created.");
    }

    public function edit(int $skill)
    {
        $skill = $this->db()->table('class_skills')->find($skill);
        abort_if(!$skill, 404);

        $classes     = $this->db()->table('classes')->orderBy('name')->get();
        $types       = ['atk', 'def', 'skill', 'heal', 'boon_atk', 'boon_def'];
        $damageTypes = ['physical', 'fire', 'poison', 'frost', 'radiant', 'dark'];

        return view('dashboard.wildhunt.skills.form', compact('skill', 'classes', 'types', 'damageTypes'));
    }

    public function update(Request $request, int $skill)
    {
        $record = $this->db()->table('class_skills')->find($skill);
        abort_if(!$record, 404);

        $validated = $request->validate([
            'slug'           => ['required', 'string', 'max:100'],
            'name'           => ['required', 'string', 'max:100'],
            'icon'           => ['required', 'string', 'max:10'],
            'class_id'       => ['nullable', 'integer'],
            'type'           => ['required', 'string'],
            'damage_type'    => ['nullable', 'string'],
            'action_cost'    => ['required', 'integer', 'min:0'],
            'value'          => ['required', 'integer', 'min:0'],
            'level_required' => ['required', 'integer', 'min:1'],
            'description'    => ['required', 'string'],
            'extra'          => ['nullable', 'string'],
        ]);

        if (!empty($validated['extra'])) {
            json_decode($validated['extra']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['extra' => 'Invalid JSON in extra.'])->withInput();
            }
        }

        $this->db()->table('class_skills')->where('id', $skill)->update([
            ...$validated,
            'class_id'   => $validated['class_id'] ?: null,
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard.wildhunt.skills.index')
            ->with('success', "Skill \"{$validated['name']}\" updated.");
    }

    public function destroy(int $skill)
    {
        $record = $this->db()->table('class_skills')->find($skill);
        abort_if(!$record, 404);

        $this->db()->table('class_skills')->where('id', $skill)->delete();

        return redirect()->route('dashboard.wildhunt.skills.index')
            ->with('success', 'Skill deleted.');
    }
}
