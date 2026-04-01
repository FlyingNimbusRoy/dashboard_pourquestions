<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Gamepack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

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
            'name'         => 'required|string|max:255',
            'category'     => 'required|in:gamepack,questionpack,skinpack',
            'flavor_text'  => 'nullable|string|max:255',
            'description'  => 'nullable|string|max:1000',
            'icon'         => 'nullable|string|max:255',
            'color'        => 'nullable|string|max:7',
            'price'        => 'required|numeric|min:0',
            'url_coverart' => 'nullable|string|max:255',
        ]);

        Gamepack::create($validated);

        return redirect()->route('dashboard.gamepacks.index')->with('success', 'Gamepack created!');
    }

    public function edit(Gamepack $gamepack)
    {
        return view('dashboard.gamepacks.form', compact('gamepack'));
    }

    public function update(Request $request, Gamepack $gamepack)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'category'     => 'required|in:gamepack,questionpack,skinpack',
            'flavor_text'  => 'nullable|string|max:255',
            'description'  => 'nullable|string|max:1000',
            'icon'         => 'nullable|string|max:255',
            'color'        => 'nullable|string|max:7',
            'price'        => 'required|numeric|min:0',
            'url_coverart' => 'nullable|string|max:255',
        ]);

        $gamepack->update($validated);

        return redirect()->route('dashboard.gamepacks.index')->with('success', 'Gamepack updated!');
    }

    public function destroy(Gamepack $gamepack)
    {
        $gamepack->delete();
        return redirect()->route('dashboard.gamepacks.index')->with('success', 'Gamepack deleted!');
    }

    public function uploadForm()
    {
        $images = File::files(public_path('img/gamepacks'));
        return view('dashboard.gamepacks.upload', compact('images'));
    }

    public function uploadStore(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $file = $request->file('image');
        $file->move(public_path('img/gamepacks'), $file->getClientOriginalName());

        return redirect()->route('dashboard.gamepacks.upload')->with('success', 'Image uploaded!');
    }

    public function uploadDestroy($filename)
    {
        $path = public_path('img/gamepacks/' . $filename);

        if (File::exists($path)) {
            File::delete($path);
            return redirect()->route('dashboard.gamepacks.upload')
                ->with('success', 'Image deleted successfully!');
        }

        return redirect()->route('dashboard.gamepacks.upload')
            ->with('error', 'Image not found.');
    }
}
