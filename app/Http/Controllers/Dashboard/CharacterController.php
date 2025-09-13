<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\Gamepack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CharacterController extends Controller
{
    public function index()
    {
        $characters = Character::with('gamepack', 'parent')->latest()->paginate(20);
        return view('dashboard.characters.index', compact('characters'));
    }

    public function create()
    {
        $gamepacks = Gamepack::all();
        $parents = Character::all();
        return view('dashboard.characters.form', compact('gamepacks', 'parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'gamepack_id' => 'nullable|exists:gamepacks,id',
            'color_primary' => 'required|string|max:7',
            'color_secondary' => 'required|string|max:7',
            'color_muted_primary' => 'required|string|max:7',
            'color_muted_secondary' => 'required|string|max:7',
            'show_on_homepage' => 'boolean',
            'parent_character_id' => 'nullable|exists:characters,id',
        ]);

        Character::create($validated);

        return redirect()->route('dashboard.characters.index')->with('success', 'Character created!');
    }

    public function edit(Character $character)
    {
        $gamepacks = Gamepack::all();
        $parents = Character::where('id', '!=', $character->id)->get();
        return view('dashboard.characters.form', compact('character', 'gamepacks', 'parents'));
    }

    public function update(Request $request, Character $character)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'gamepack_id' => 'nullable|exists:gamepacks,id',
            'color_primary' => 'required|string|max:7',
            'color_secondary' => 'required|string|max:7',
            'color_muted_primary' => 'required|string|max:7',
            'color_muted_secondary' => 'required|string|max:7',
            'show_on_homepage' => 'boolean',
            'parent_character_id' => 'nullable|exists:characters,id',
        ]);

        $character->update($validated);

        return redirect()->route('dashboard.characters.index')->with('success', 'Character updated!');
    }

    public function show(Character $character)
    {
        // Optional: just redirect back to index
        return redirect()->route('dashboard.characters.index');
    }

    public function destroy(Character $character)
    {
        $character->delete();
        return redirect()->route('dashboard.characters.index')->with('success', 'Character deleted!');
    }

    public function uploadForm()
    {
        // Get all images from public/img/characters
        $images = File::files(public_path('img/characters'));
        return view('dashboard.characters.upload', compact('images'));
    }

    public function uploadStore(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120', // max 5MB
        ]);

        $file = $request->file('image');
        $file->move(public_path('img/characters'), $file->getClientOriginalName());

        return redirect()->route('dashboard.characters.upload')->with('success', 'Image uploaded!');
    }

    public function uploadDestroy($filename)
    {
        $path = public_path('img/characters/' . $filename);

        if (File::exists($path)) {
            File::delete($path);
            return redirect()->route('dashboard.characters.upload')
                ->with('success', 'Image deleted successfully!');
        }

        return redirect()->route('dashboard.characters.upload')
            ->with('error', 'Image not found.');
    }

}
