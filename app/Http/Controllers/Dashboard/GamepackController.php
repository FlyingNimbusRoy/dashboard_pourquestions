<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Gamepack;
use Illuminate\Http\Request;

class GamepackController extends Controller
{
    public function index()
    {
        $gamepacks = Gamepack::latest()->paginate(20);
        return view('dashboard.gamepacks.index', compact('gamepacks'));
    }

    public function create()
    {
        return view('dashboard.gamepacks.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'price' => 'required|numeric|min:0',
            'url_coverart' => 'nullable|url|max:255',
        ]);

        Gamepack::create($validated);

        return redirect()->route('dashboard.gamepacks.index')->with('success', 'Gamepack created!');
    }

    public function edit(Gamepack $gamepack)
    {
        return view('dashboard.gamepacks.edit', compact('gamepack'));
    }

    public function update(Request $request, Gamepack $gamepack)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'price' => 'required|numeric|min:0',
            'url_coverart' => 'nullable|url|max:255',
        ]);

        $gamepack->update($validated);

        return redirect()->route('dashboard.gamepacks.index')->with('success', 'Gamepack updated!');
    }

    public function destroy(Gamepack $gamepack)
    {
        $gamepack->delete();
        return redirect()->route('dashboard.gamepacks.index')->with('success', 'Gamepack deleted!');
    }
}
