<?php

namespace App\Http\Controllers\DashboardWildhunt;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
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

        // Attach monsters count to each quarry
        $quarries = $quarries->map(function ($q) {
            $q->monster_count = $this->db()
                ->table('monsters')
                ->where('hunt_quarry_id', $q->id)
                ->count();
            return $q;
        });

        return view('dashboard.wildhunt.quarries.index', compact('quarries'));
    }

    public function create()
    {
        return view('dashboard.wildhunt.quarries.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'            => ['required', 'string', 'max:100'],
            'title'           => ['required', 'string', 'max:150'],
            'description'     => ['required', 'string'],
            'tier'            => ['required', 'integer', 'min:1', 'max:6'],
            'min_level'       => ['required', 'integer', 'min:1'],
        ]);

        $this->db()->table('hunt_quarries')->insert([
            ...$validated,
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

        $monsters = $this->db()
            ->table('monsters')
            ->where('hunt_quarry_id', $quarry->id)
            ->orderBy('is_boss', 'desc')
            ->orderBy('level')
            ->get();

        return view('dashboard.wildhunt.quarries.form', compact('quarry', 'monsters'));
    }

    public function update(Request $request, int $quarry)
    {
        $record = $this->db()->table('hunt_quarries')->find($quarry);
        abort_if(!$record, 404);

        $validated = $request->validate([
            'type'        => ['required', 'string', 'max:100'],
            'title'       => ['required', 'string', 'max:150'],
            'description' => ['required', 'string'],
            'tier'        => ['required', 'integer', 'min:1', 'max:6'],
            'min_player_level'   => ['required', 'integer', 'min:1'],
        ]);

        $this->db()->table('hunt_quarries')->where('id', $quarry)->update([
            ...$validated,
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
}
