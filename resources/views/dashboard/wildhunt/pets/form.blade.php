@extends('dashboard')
@section('content')

    <h1 class="text-2xl font-bold mb-4">
        {{ isset($pet) ? 'Edit ' . $pet->name : 'New Pet Definition' }}
    </h1>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ isset($pet) ? route('dashboard.wildhunt.pets.update', $pet->id) : route('dashboard.wildhunt.pets.store') }}"
          class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-6">
        @csrf
        @if(isset($pet)) @method('PUT') @endif

        {{-- Identity --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Identity</h2>
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $pet->name ?? '') }}"
                           class="w-full border rounded py-2 px-3">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Slug <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(unique identifier, e.g. lynx)</span></label>
                    <input type="text" name="slug" value="{{ old('slug', $pet->slug ?? '') }}"
                           class="w-full border rounded py-2 px-3 font-mono text-sm"
                           placeholder="lynx">
                    @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Default Icon <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(emoji fallback, e.g. 🐾)</span></label>
                    <input type="text" name="icon" value="{{ old('icon', $pet->icon ?? '🐾') }}"
                           class="w-full border rounded py-2 px-3 text-xl" maxlength="10">
                    @error('icon') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Personality <span class="text-red-500">*</span></label>
                    <select name="personality" class="w-full border rounded py-2 px-3">
                        @foreach(['aggressive','support'] as $opt)
                            <option value="{{ $opt }}" {{ old('personality', $pet->personality ?? 'aggressive') === $opt ? 'selected' : '' }}>
                                {{ ucfirst($opt) }}
                            </option>
                        @endforeach
                    </select>
                    @error('personality') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                    <textarea name="description" rows="2"
                              class="w-full border rounded py-2 px-3 text-sm">{{ old('description', $pet->description ?? '') }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Classification --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Classification</h2>
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Rarity <span class="text-red-500">*</span></label>
                    <select name="rarity" class="w-full border rounded py-2 px-3">
                        @foreach(['bonded','common','rare','legendary'] as $opt)
                            <option value="{{ $opt }}" {{ old('rarity', $pet->rarity ?? 'bonded') === $opt ? 'selected' : '' }}>
                                {{ ucfirst($opt) }}
                            </option>
                        @endforeach
                    </select>
                    @error('rarity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Source <span class="text-red-500">*</span></label>
                    <select name="source" class="w-full border rounded py-2 px-3">
                        @foreach(['class','quest','event','drop'] as $opt)
                            <option value="{{ $opt }}" {{ old('source', $pet->source ?? 'class') === $opt ? 'selected' : '' }}>
                                {{ ucfirst($opt) }}
                            </option>
                        @endforeach
                    </select>
                    @error('source') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Stats --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Stats & Scaling</h2>
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">HP Multiplier <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(fraction of owner HP)</span></label>
                    <input type="number" step="0.01" name="hp_multiplier"
                           value="{{ old('hp_multiplier', $pet->hp_multiplier ?? '0.50') }}"
                           min="0" class="w-full border rounded py-2 px-3">
                    @error('hp_multiplier') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Attack Multiplier <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(fraction of owner attack)</span></label>
                    <input type="number" step="0.01" name="attack_multiplier"
                           value="{{ old('attack_multiplier', $pet->attack_multiplier ?? '0.70') }}"
                           min="0" class="w-full border rounded py-2 px-3">
                    @error('attack_multiplier') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Min HP
                        <span class="text-gray-400 font-normal">(hard floor, 0 = none)</span></label>
                    <input type="number" name="min_hp"
                           value="{{ old('min_hp', $pet->min_hp ?? 0) }}"
                           min="0" class="w-full border rounded py-2 px-3">
                    @error('min_hp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Min Attack
                        <span class="text-gray-400 font-normal">(hard floor, 0 = none)</span></label>
                    <input type="number" name="min_attack"
                           value="{{ old('min_attack', $pet->min_attack ?? 0) }}"
                           min="0" class="w-full border rounded py-2 px-3">
                    @error('min_attack') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Behaviour --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Behaviour</h2>
            <div class="grid grid-cols-2 gap-4">

                <div class="flex items-center gap-2 pt-2">
                    <input type="hidden" name="taunt" value="0">
                    <input type="checkbox" name="taunt" id="taunt" value="1"
                           {{ old('taunt', $pet->taunt ?? false) ? 'checked' : '' }}>
                    <label for="taunt" class="text-gray-700 text-sm font-bold">Taunt
                        <span class="text-gray-400 font-normal">(intercepts enemy attacks)</span></label>
                    @error('taunt') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Passives
                        <span class="text-gray-400 font-normal">JSON — e.g. {"bleed":2}</span></label>
                    <input type="text" name="passives"
                           value="{{ old('passives', $pet->passives ?? '') }}"
                           placeholder='{"bleed":2}'
                           class="w-full border rounded py-2 px-3 font-mono text-sm">
                    @error('passives') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Actions by Level
                        <span class="text-gray-400 font-normal">JSON array — e.g. [[1,1],[10,2]] (level threshold → action count)</span></label>
                    <input type="text" name="actions_by_level"
                           value="{{ old('actions_by_level', $pet->actions_by_level ?? '[[1,1]]') }}"
                           placeholder='[[1,1],[10,2]]'
                           class="w-full border rounded py-2 px-3 font-mono text-sm">
                    @error('actions_by_level') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ isset($pet) ? 'Update Pet' : 'Create Pet' }}
            </button>
            <a href="{{ route('dashboard.wildhunt.pets.index') }}"
               class="text-blue-500 hover:underline">Cancel</a>
        </div>

    </form>

    @if(isset($pet))
        <div class="mt-2">
            <a href="{{ route('dashboard.wildhunt.pets.icons', $pet->id) }}"
               class="text-purple-600 hover:underline text-sm">🖼 Manage PNG icons for this pet →</a>
        </div>
    @endif

@endsection
