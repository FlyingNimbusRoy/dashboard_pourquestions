<?php

namespace App\Http\Controllers\DashboardWildhunt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WildhuntPetController extends Controller
{
    private function db() { return DB::connection('wildhunt'); }

    private function iconDir(string $slug): string
    {
        $base = rtrim(env('WILDHUNT_APP_PATH', base_path('../pourspireadventures')), '/\\');
        return $base . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR
             . 'icons' . DIRECTORY_SEPARATOR . 'pets' . DIRECTORY_SEPARATOR . $slug;
    }

    // ── Index ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = $this->db()->table('pet_definitions');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->filled('rarity')) {
            $query->where('rarity', $request->rarity);
        }

        $pets = $query->orderBy('id')->paginate(40);
        $pets->appends($request->only('search', 'rarity'));

        return view('dashboard.wildhunt.pets.index', compact('pets'));
    }

    // ── Create / Store ────────────────────────────────────────────────────────
    public function create()
    {
        return view('dashboard.wildhunt.pets.form');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePet($request);
        if ($validated instanceof \Illuminate\Http\RedirectResponse) return $validated;

        $this->db()->table('pet_definitions')->insert([
            ...$validated,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard.wildhunt.pets.index')
            ->with('success', "Pet \"{$validated['name']}\" created.");
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────
    public function edit(int $pet)
    {
        $pet = $this->db()->table('pet_definitions')->find($pet);
        abort_if(!$pet, 404);
        return view('dashboard.wildhunt.pets.form', compact('pet'));
    }

    public function update(Request $request, int $pet)
    {
        $record = $this->db()->table('pet_definitions')->find($pet);
        abort_if(!$record, 404);

        $validated = $this->validatePet($request, $record->slug);
        if ($validated instanceof \Illuminate\Http\RedirectResponse) return $validated;

        $this->db()->table('pet_definitions')->where('id', $pet)->update([
            ...$validated,
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard.wildhunt.pets.index')
            ->with('success', "Pet \"{$validated['name']}\" updated.");
    }

    // ── Destroy ───────────────────────────────────────────────────────────────
    public function destroy(int $pet)
    {
        $record = $this->db()->table('pet_definitions')->find($pet);
        abort_if(!$record, 404);

        $this->db()->table('pet_definitions')->where('id', $pet)->delete();

        return redirect()->route('dashboard.wildhunt.pets.index')
            ->with('success', 'Pet deleted.');
    }

    // ── Icon management ───────────────────────────────────────────────────────
    public function icons(int $pet)
    {
        $record = $this->db()->table('pet_definitions')->find($pet);
        abort_if(!$record, 404);

        $dir   = $this->iconDir($record->slug);
        $files = [];
        if (is_dir($dir)) {
            foreach (glob("{$dir}/*.png") ?: [] as $path) {
                $files[] = basename($path);
            }
            sort($files);
        }

        return view('dashboard.wildhunt.pets.icons', compact('record', 'files'));
    }

    public function uploadIcon(Request $request, int $pet)
    {
        $record = $this->db()->table('pet_definitions')->find($pet);
        abort_if(!$record, 404);

        $request->validate([
            'icon' => ['required', 'file', 'mimes:png', 'max:512'],
        ]);

        $dir = $this->iconDir($record->slug);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $original = $request->file('icon')->getClientOriginalName();
        // Sanitise: lowercase, spaces→underscore, only word chars/hyphens/dots
        $filename = Str::lower(preg_replace('/[^\w\-.]/', '_', $original));
        if (!str_ends_with($filename, '.png')) $filename .= '.png';

        $request->file('icon')->move($dir, $filename);

        return redirect()->route('dashboard.wildhunt.pets.icons', $pet)
            ->with('success', "Icon \"{$filename}\" uploaded.");
    }

    public function previewIcon(int $pet, string $filename)
    {
        $record = $this->db()->table('pet_definitions')->find($pet);
        abort_if(!$record, 404);
        abort_if(!preg_match('/^[\w\-]+\.png$/', $filename), 422);

        $path = $this->iconDir($record->slug) . DIRECTORY_SEPARATOR . $filename;
        abort_if(!file_exists($path), 404);

        return response()->file($path, ['Content-Type' => 'image/png']);
    }

    public function destroyIcon(Request $request, int $pet, string $filename)
    {
        $record = $this->db()->table('pet_definitions')->find($pet);
        abort_if(!$record, 404);

        // Only allow safe filenames
        abort_if(!preg_match('/^[\w\-]+\.png$/', $filename), 422);

        $path = $this->iconDir($record->slug) . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($path)) {
            unlink($path);
        }

        return redirect()->route('dashboard.wildhunt.pets.icons', $pet)
            ->with('success', "Icon \"{$filename}\" deleted.");
    }

    // ── Shared validation ─────────────────────────────────────────────────────
    private function validatePet(Request $request, ?string $currentSlug = null)
    {
        $validated = $request->validate([
            'slug'              => ['required', 'string', 'max:60'],
            'name'              => ['required', 'string', 'max:100'],
            'icon'              => ['required', 'string', 'max:10'],
            'description'       => ['nullable', 'string'],
            'rarity'            => ['required', 'string', 'in:bonded,common,rare,legendary'],
            'source'            => ['required', 'string', 'in:class,quest,event,drop'],
            'hp_multiplier'     => ['required', 'numeric', 'min:0'],
            'attack_multiplier' => ['required', 'numeric', 'min:0'],
            'min_hp'            => ['required', 'integer', 'min:0'],
            'min_attack'        => ['required', 'integer', 'min:0'],
            'passives'          => ['nullable', 'string'],
            'taunt'             => ['boolean'],
            'personality'       => ['required', 'string', 'in:aggressive,support'],
            'actions_by_level'  => ['nullable', 'string'],
        ]);

        // JSON field validation
        foreach (['passives', 'actions_by_level'] as $field) {
            if (!empty($validated[$field])) {
                json_decode($validated[$field]);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->withErrors([$field => "Invalid JSON in {$field}."])->withInput();
                }
            }
        }

        // Unique slug check (exclude self on update)
        $slugQuery = $this->db()->table('pet_definitions')->where('slug', $validated['slug']);
        if ($currentSlug !== null) {
            $slugQuery->where('slug', '!=', $currentSlug);
        }
        if ($slugQuery->exists()) {
            return back()->withErrors(['slug' => 'This slug is already taken.'])->withInput();
        }

        return array_merge($validated, ['taunt' => $request->boolean('taunt')]);
    }
}
