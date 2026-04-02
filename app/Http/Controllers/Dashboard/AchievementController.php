<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AchievementController extends Controller
{
    public function index()
    {
        $achievements = Achievement::latest()->paginate(20);
        return view('dashboard.achievements.index', compact('achievements'));
    }

    public function create()
    {
        return view('dashboard.achievements.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'required|string|max:1000',
            'image'          => 'required|string|max:255',
            'criteria_type'  => 'required|in:games_played,games_won,total_shots,shot_highscore',
            'criteria_value' => 'required|integer|min:1',
        ]);

        Achievement::create($validated);

        return redirect()->route('dashboard.achievements.index')->with('success', 'Achievement created!');
    }

    public function edit(Achievement $achievement)
    {
        return view('dashboard.achievements.form', compact('achievement'));
    }

    public function update(Request $request, Achievement $achievement)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'required|string|max:1000',
            'image'          => 'required|string|max:255',
            'criteria_type'  => 'required|in:games_played,games_won,total_shots,shot_highscore',
            'criteria_value' => 'required|integer|min:1',
        ]);

        $achievement->update($validated);

        return redirect()->route('dashboard.achievements.index')->with('success', 'Achievement updated!');
    }

    public function destroy(Achievement $achievement)
    {
        $achievement->delete();
        return redirect()->route('dashboard.achievements.index')->with('success', 'Achievement deleted!');
    }

    public function uploadForm()
    {
        $images = File::files(public_path('img/achievements'));
        return view('dashboard.achievements.upload', compact('images'));
    }

    public function uploadStore(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
        ]);

        $file = $request->file('image');
        $file->move(public_path('img/achievements'), $file->getClientOriginalName());

        return redirect()->route('dashboard.achievements.upload')->with('success', 'Image uploaded!');
    }

    public function uploadDestroy($filename)
    {
        $path = public_path('img/achievements/' . $filename);

        if (File::exists($path)) {
            File::delete($path);
            return redirect()->route('dashboard.achievements.upload')
                ->with('success', 'Image deleted successfully!');
        }

        return redirect()->route('dashboard.achievements.upload')
            ->with('error', 'Image not found.');
    }
}