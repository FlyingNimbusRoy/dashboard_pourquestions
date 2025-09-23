<?php

namespace App\Http\Controllers;

use App\Models\Modifier;
use App\Models\Gamepack;
use Illuminate\Http\Request;

class ModifierController extends Controller
{
    /**
     * Toon een lijst van alle modifiers.
     */
    public function index()
    {
        $modifiers = Modifier::with('gamepack')->paginate(20);
        return view('modifiers.index', compact('modifiers'));
    }

    /**
     * Laat het formulier zien om een nieuwe modifier te maken.
     */
    public function create()
    {
        $gamepacks = Gamepack::all();
        return view('modifiers.create', compact('gamepacks'));
    }

    /**
     * Sla een nieuwe modifier op in de database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'fa_icon'            => 'nullable|string|max:255',
            'turnbased'          => 'boolean',
            'effects'            => 'nullable|json',
            'coupled_gamepack_id'=> 'nullable|exists:gamepacks,id',
            'is_active'          => 'boolean',
        ]);

        Modifier::create($validated);

        return redirect()->route('modifiers.index')->with('success', 'Modifier created successfully.');
    }

    /**
     * Laat een formulier zien om een bestaande modifier te bewerken.
     */
    public function edit(Modifier $modifier)
    {
        $gamepacks = Gamepack::all();
        return view('modifiers.edit', compact('modifier', 'gamepacks'));
    }

    /**
     * Update een bestaande modifier in de database.
     */
    public function update(Request $request, Modifier $modifier)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'fa_icon'            => 'nullable|string|max:255',
            'turnbased'          => 'boolean',
            'effects'            => 'nullable|json',
            'coupled_gamepack_id'=> 'nullable|exists:gamepacks,id',
            'is_active'          => 'boolean',
        ]);

        $modifier->update($validated);

        return redirect()->route('modifiers.index')->with('success', 'Modifier updated successfully.');
    }

    /**
     * Verwijder een modifier uit de database.
     */
    public function destroy(Modifier $modifier)
    {
        $modifier->delete();
        return redirect()->route('modifiers.index')->with('success', 'Modifier deleted successfully.');
    }
}
