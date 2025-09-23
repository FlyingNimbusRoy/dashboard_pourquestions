<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Modifier;
use App\Models\Gamepack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ModifierController extends Controller
{
    /**
     * Toon een lijst van alle modifiers.
     */
    public function index()
    {
        $modifiers = Modifier::with('gamepack')->paginate(20);

        return view('dashboard.modifiers.index', compact('modifiers'));
    }

    /**
     * Laat het formulier zien om een nieuwe modifier te maken.
     */
    public function create()
    {
        $gamepacks = Gamepack::all();

        return view('dashboard.modifiers.form', compact('gamepacks'));
    }

    /**
     * Sla een nieuwe modifier op in de database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'turnbased' => 'boolean',
            'effects' => 'nullable|json',
            'coupled_gamepack_id' => 'nullable|exists:gamepacks,id',
            'is_active' => 'boolean',
        ]);

        Modifier::create($validated);

        return redirect()->route('dashboard.modifiers.index')->with('success', 'Modifier created successfully.');
    }

    /**
     * Laat een formulier zien om een bestaande modifier te bewerken.
     */
    public function edit(Modifier $modifier)
    {
        $gamepacks = Gamepack::all();

        return view('dashboard.modifiers.form', compact('modifier', 'gamepacks'));
    }

    /**
     * Update een bestaande modifier in de database.
     */
    public function update(Request $request, Modifier $modifier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'turnbased' => 'boolean',
            'effects' => 'nullable|json',
            'coupled_gamepack_id' => 'nullable|exists:gamepacks,id',
            'is_active' => 'boolean',
        ]);

        $modifier->update($validated);

        return redirect()->route('dashboard.modifiers.index')->with('success', 'Modifier updated successfully.');
    }

    /**
     * Verwijder een modifier uit de database.
     */
    public function destroy(Modifier $modifier)
    {
        $modifier->delete();

        return redirect()->route('dashboard.modifiers.index')->with('success', 'Modifier deleted successfully.');
    }

    /**
     * Uploaden van bestanden in folder tool
     */
    /**
     * Uploaden van bestanden in folder tool
     */
    public function uploadForm()
    {
        $path = public_path('img/modifiers');
        $images = File::exists($path) ? File::files($path) : [];

        return view('dashboard.modifiers.upload', compact('images'));
    }

    public function uploadStore(Request $request)
    {
        $request->validate([
            // Accepteer jpg, jpeg, png, gif, webp en svg
            'image' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg|max:4096',
        ]);

        $file = $request->file('image');
        $filename = time().'_'.$file->getClientOriginalName();

        $destination = public_path('img/modifiers');

        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        // SVG los opslaan, andere direct moven
        if (strtolower($file->getClientOriginalExtension()) === 'svg') {
            $svgContent = file_get_contents($file->getRealPath());

            // optioneel: hier sanitizeSvg($svgContent) aanroepen
            File::put($destination.'/'.$filename, $svgContent);
        } else {
            $file->move($destination, $filename);
        }

        return redirect()
            ->route('dashboard.modifiers.upload')
            ->with('success', 'Modifier image uploaded successfully!');
    }

    public function uploadDestroy($filename)
    {
        $path = public_path('img/modifiers/'.$filename);
        if (File::exists($path)) {
            File::delete($path);

            return redirect()
                ->route('dashboard.modifiers.upload')
                ->with('success', 'Modifier image deleted successfully!');
        }

        return redirect()
            ->route('dashboard.modifiers.upload')
            ->with('error', 'File not found.');
    }

}
