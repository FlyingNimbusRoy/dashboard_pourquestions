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
          class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-6">
        @csrf
        @if(isset($monster)) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4">

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                <input type="text" name="name" value="{{ old('name', $monster->name ?? '') }}"
                       class="w-full border rounded py-2 px-3">
                @error('name') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Icon <span class="text-gray-400 font-normal">(emoji or filename.png)</span></label>
                <input type="text" name="icon" value="{{ old('icon', $monster->icon ?? '') }}"
                       class="w-full border rounded py-2 px-3">
                @error('icon') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-2">
                <label class="block text-gray-700 text-sm font-bold mb-2">Quarry</label>
                <select name="hunt_quarry_id" class="w-full border rounded py-2 px-3">
                    <option value="">— select quarry —</option>
                    @foreach($quarries as $q)
                        <option value="{{ $q->id }}"
                                {{ old('hunt_quarry_id', $monster->hunt_quarry_id ?? $selectedQuarry ?? '') == $q->id ? 'selected' : '' }}>
                            [T{{ $q->tier }}] {{ $q->title }}
                        </option>
                    @endforeach
                </select>
                @error('hunt_quarry_id') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Level</label>
                <input type="number" name="level" value="{{ old('level', $monster->level ?? 1) }}" min="1"
                       class="w-full border rounded py-2 px-3">
                @error('level') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">HP</label>
                <input type="number" name="hp" value="{{ old('hp', $monster->hp ?? 50) }}" min="1"
                       class="w-full border rounded py-2 px-3">
                @error('hp') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Defense</label>
                <input type="number" name="defense" value="{{ old('defense', $monster->defense ?? 0) }}" min="0"
                       class="w-full border rounded py-2 px-3">
                @error('defense') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-2 pt-6">
                <input type="hidden" name="is_boss" value="0">
                <input type="checkbox" name="is_boss" id="is_boss" value="1"
                        {{ old('is_boss', $monster->is_boss ?? false) ? 'checked' : '' }}>
                <label for="is_boss" class="text-gray-700 text-sm font-bold">Boss (wave 5)</label>
                @error('is_boss') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Exp reward</label>
                <input type="number" name="exp_reward" value="{{ old('exp_reward', $monster->exp_reward ?? 50) }}" min="0"
                       class="w-full border rounded py-2 px-3">
                @error('exp_reward') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Crest min</label>
                <input type="number" name="crest_min" value="{{ old('crest_min', $monster->crest_min ?? 10) }}" min="0"
                       class="w-full border rounded py-2 px-3">
                @error('crest_min') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Crest max</label>
                <input type="number" name="crest_max" value="{{ old('crest_max', $monster->crest_max ?? 25) }}" min="0"
                       class="w-full border rounded py-2 px-3">
                @error('crest_max') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-2">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Resistances <span class="text-gray-400 font-normal">JSON array e.g. ["fire","frost"]</span>
                </label>
                <input type="text" name="resistances" value="{{ old('resistances', $monster->resistances ?? '') }}"
                       placeholder='["fire"]' class="w-full border rounded py-2 px-3 font-mono text-sm">
                @error('resistances') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-2">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Vulnerabilities <span class="text-gray-400 font-normal">JSON array e.g. ["frost"]</span>
                </label>
                <input type="text" name="vulnerabilities" value="{{ old('vulnerabilities', $monster->vulnerabilities ?? '') }}"
                       placeholder='["frost"]' class="w-full border rounded py-2 px-3 font-mono text-sm">
                @error('vulnerabilities') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-2">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Personality <span class="text-gray-400 font-normal">JSON object — controls AI scoring weights</span>
                </label>
                <textarea name="personality" rows="3"
                          placeholder='{"aggression": 0.8, "condition": 0.5}'
                          class="w-full border rounded py-2 px-3 font-mono text-sm">{{ old('personality', $monster->personality ?? '') }}</textarea>
                @error('personality') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-2">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Moves <span class="text-red-500">*</span>
                    <span class="text-gray-400 font-normal">JSON array of move objects</span>
                </label>
                <textarea name="moves" rows="12" required
                          placeholder='[{"name":"Strike","type":"atk","damage_type":"physical","value":10,"extra":null,"icon":"⚔","description":"A basic strike."}]'
                          class="w-full border rounded py-2 px-3 font-mono text-sm">{{ old('moves', $monster->moves ?? '') }}</textarea>
                @error('moves') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

        </div>

        <div class="flex items-center justify-between mt-4">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ isset($monster) ? 'Update Monster' : 'Create Monster' }}
            </button>
            <a href="{{ route('dashboard.wildhunt.monsters.index') }}"
               class="text-blue-500 hover:underline">Cancel</a>
        </div>

    </form>
@endsection