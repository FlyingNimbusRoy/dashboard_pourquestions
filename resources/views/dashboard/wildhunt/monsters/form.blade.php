@extends('dashboard')
@section('content')

    <h1 class="text-2xl font-bold mb-4">
        {{ isset($monster) ? 'Edit ' . $monster->name : 'New Monster' }}
    </h1>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ isset($monster) ? route('dashboard.wildhunt.monsters.update', $monster->id) : route('dashboard.wildhunt.monsters.store') }}"
          class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-8">
        @csrf
        @if(isset($monster)) @method('PUT') @endif

        {{-- Identity --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Identity</h2>
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $monster->name ?? '') }}"
                           class="w-full border rounded py-2 px-3">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Icon <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(emoji or filename.png)</span></label>
                    <input type="text" name="icon" value="{{ old('icon', $monster->icon ?? '') }}"
                           class="w-full border rounded py-2 px-3">
                    @error('icon') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Description
                        <span class="text-gray-400 font-normal">(flavour text, optional)</span></label>
                    <textarea name="description" rows="2"
                              class="w-full border rounded py-2 px-3 text-sm">{{ old('description', $monster->description ?? '') }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Quarry <span class="text-red-500">*</span></label>
                    <select name="hunt_quarry_id" class="w-full border rounded py-2 px-3">
                        <option value="">— select quarry —</option>
                        @foreach($quarries as $q)
                            <option value="{{ $q->id }}"
                                    {{ old('hunt_quarry_id', $monster->hunt_quarry_id ?? $selectedQuarry ?? '') == $q->id ? 'selected' : '' }}>
                                [T{{ $q->tier }}] {{ $q->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('hunt_quarry_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Level <span class="text-red-500">*</span></label>
                    <input type="number" name="level" value="{{ old('level', $monster->level ?? 1) }}" min="1"
                           class="w-full border rounded py-2 px-3">
                    @error('level') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Stats --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Stats</h2>
            <div class="grid grid-cols-3 gap-4">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">HP <span class="text-red-500">*</span></label>
                    <input type="number" name="hp" value="{{ old('hp', $monster->hp ?? 50) }}" min="1"
                           class="w-full border rounded py-2 px-3">
                    @error('hp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Defense <span class="text-red-500">*</span></label>
                    <input type="number" name="defense" value="{{ old('defense', $monster->defense ?? 0) }}" min="0"
                           class="w-full border rounded py-2 px-3">
                    @error('defense') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Max Actions <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(moves per turn)</span></label>
                    <input type="number" name="max_actions" value="{{ old('max_actions', $monster->max_actions ?? 1) }}" min="1" max="10"
                           class="w-full border rounded py-2 px-3">
                    @error('max_actions') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Rewards & Loot --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Rewards & Loot</h2>
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Exp Reward <span class="text-red-500">*</span></label>
                    <input type="number" name="exp_reward" value="{{ old('exp_reward', $monster->exp_reward ?? 50) }}" min="0"
                           class="w-full border rounded py-2 px-3">
                    @error('exp_reward') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Crest Min <span class="text-red-500">*</span></label>
                        <input type="number" name="crest_min" value="{{ old('crest_min', $monster->crest_min ?? 10) }}" min="0"
                               class="w-full border rounded py-2 px-3">
                        @error('crest_min') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Crest Max <span class="text-red-500">*</span></label>
                        <input type="number" name="crest_max" value="{{ old('crest_max', $monster->crest_max ?? 25) }}" min="0"
                               class="w-full border rounded py-2 px-3">
                        @error('crest_max') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Loot Chance <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(% gear drop chance, 0–100)</span></label>
                    <input type="number" name="loot_chance" value="{{ old('loot_chance', $monster->loot_chance ?? 0) }}" min="0" max="100"
                           class="w-full border rounded py-2 px-3">
                    @error('loot_chance') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Loot Min Value <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(min crest_cost of droppable gear)</span></label>
                    <input type="number" name="loot_min_value" value="{{ old('loot_min_value', $monster->loot_min_value ?? 0) }}" min="0"
                           class="w-full border rounded py-2 px-3">
                    @error('loot_min_value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Classification --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Classification</h2>
            <div class="grid grid-cols-3 gap-4">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Personality <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(AI profile)</span></label>
                    <select name="personality" class="w-full border rounded py-2 px-3">
                        @foreach($personalities as $p)
                            <option value="{{ $p }}"
                                    {{ old('personality', $monster->personality ?? 'default') === $p ? 'selected' : '' }}>
                                {{ ucfirst($p) }}
                            </option>
                        @endforeach
                    </select>
                    @error('personality') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Difficulty <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(1–5, used for wave ordering)</span></label>
                    <select name="difficulty" class="w-full border rounded py-2 px-3">
                        @foreach(range(1,5) as $d)
                            <option value="{{ $d }}"
                                    {{ old('difficulty', $monster->difficulty ?? 1) == $d ? 'selected' : '' }}>
                                {{ $d }}
                            </option>
                        @endforeach
                    </select>
                    @error('difficulty') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2 pt-6">
                    <input type="hidden" name="is_boss" value="0">
                    <input type="checkbox" name="is_boss" id="is_boss" value="1"
                           {{ old('is_boss', $monster->is_boss ?? false) ? 'checked' : '' }}>
                    <label for="is_boss" class="text-gray-700 text-sm font-bold">Boss
                        <span class="text-gray-400 font-normal">(appears on wave 5)</span></label>
                    @error('is_boss') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Resistances & Vulnerabilities --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Resistances & Vulnerabilities</h2>
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Resistances
                        <span class="text-gray-400 font-normal">JSON array — e.g. ["fire","frost"]</span></label>
                    <input type="text" name="resistances" value="{{ old('resistances', $monster->resistances ?? '') }}"
                           placeholder='["fire"]' class="w-full border rounded py-2 px-3 font-mono text-sm">
                    @error('resistances') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Vulnerabilities
                        <span class="text-gray-400 font-normal">JSON array — e.g. ["frost"]</span></label>
                    <input type="text" name="vulnerabilities" value="{{ old('vulnerabilities', $monster->vulnerabilities ?? '') }}"
                           placeholder='["frost"]' class="w-full border rounded py-2 px-3 font-mono text-sm">
                    @error('vulnerabilities') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Phases --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Phases
                <span class="text-gray-400 font-normal text-xs normal-case ml-1">(optional — boss behaviour changes at HP thresholds)</span>
            </h2>
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Phases JSON
                <span class="text-gray-400 font-normal">array of phase objects; leave blank for single-phase monsters</span>
            </label>
            <textarea name="phases" rows="5"
                      placeholder='[{"at_hp_pct":0.5,"force_move":null,"personality_override":"berserker","once":true}]'
                      class="w-full border rounded py-2 px-3 font-mono text-sm">{{ old('phases', $monster->phases ?? '') }}</textarea>
            <p class="text-gray-400 text-xs mt-1">
                Keys: <code class="bg-gray-100 px-1 rounded">at_hp_pct</code> (0.0–1.0 HP fraction),
                <code class="bg-gray-100 px-1 rounded">force_move</code> (move name or null),
                <code class="bg-gray-100 px-1 rounded">personality_override</code> (replaces AI after threshold),
                <code class="bg-gray-100 px-1 rounded">once</code> (boolean — trigger only once)
            </p>
            @error('phases') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Moves --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Moves <span class="text-red-500 normal-case text-xs">required</span></h2>
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Moves JSON
                <span class="text-gray-400 font-normal">array of move objects</span>
            </label>
            <textarea name="moves" rows="14" required
                      placeholder='[{"name":"Strike","type":"atk","damage_type":"physical","value":10,"extra":null,"icon":"⚔","description":"A basic strike."}]'
                      class="w-full border rounded py-2 px-3 font-mono text-sm">{{ old('moves', $monster->moves ?? '') }}</textarea>
            <p class="text-gray-400 text-xs mt-1">
                Keys: <code class="bg-gray-100 px-1 rounded">name</code>,
                <code class="bg-gray-100 px-1 rounded">type</code> (atk/def/heal/condition/enrage),
                <code class="bg-gray-100 px-1 rounded">damage_type</code> (physical/fire/frost/poison/radiant/dark),
                <code class="bg-gray-100 px-1 rounded">value</code>,
                <code class="bg-gray-100 px-1 rounded">icon</code>,
                <code class="bg-gray-100 px-1 rounded">description</code>,
                <code class="bg-gray-100 px-1 rounded">extra</code>
            </p>
            @error('moves') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between pt-2">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ isset($monster) ? 'Update Monster' : 'Create Monster' }}
            </button>
            <a href="{{ route('dashboard.wildhunt.monsters.index') }}"
               class="text-blue-500 hover:underline">Cancel</a>
        </div>

    </form>
@endsection
