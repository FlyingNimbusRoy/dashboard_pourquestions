@extends('dashboard')
@section('content')

    <h1 class="text-2xl font-bold mb-4">
        {{ isset($quarry) ? 'Edit ' . $quarry->title : 'New Quarry' }}
    </h1>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ isset($quarry) ? route('dashboard.wildhunt.quarries.update', $quarry->id) : route('dashboard.wildhunt.quarries.store') }}"
          class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-8">
        @csrf
        @if(isset($quarry)) @method('PUT') @endif

        {{-- Identity --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Identity</h2>
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $quarry->title ?? '') }}"
                           class="w-full border rounded py-2 px-3">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Type slug <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(e.g. mount_vorfang)</span></label>
                    <input type="text" name="type" value="{{ old('type', $quarry->type ?? '') }}"
                           class="w-full border rounded py-2 px-3 font-mono">
                    @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tier <span class="text-red-500">*</span></label>
                    <select name="tier" class="w-full border rounded py-2 px-3">
                        @foreach(range(1, 6) as $t)
                            <option value="{{ $t }}" {{ old('tier', $quarry->tier ?? 1) == $t ? 'selected' : '' }}>
                                Tier {{ $t }}
                            </option>
                        @endforeach
                    </select>
                    @error('tier') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Min Player Level <span class="text-red-500">*</span></label>
                    <input type="number" name="min_player_level"
                           value="{{ old('min_player_level', $quarry->min_player_level ?? 1) }}" min="1"
                           class="w-full border rounded py-2 px-3">
                    @error('min_player_level') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Description <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="4"
                              class="w-full border rounded py-2 px-3">{{ old('description', $quarry->description ?? '') }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="hidden" name="multiplayer_only" value="0">
                    <input type="checkbox" name="multiplayer_only" id="multiplayer_only" value="1"
                           {{ old('multiplayer_only', $quarry->multiplayer_only ?? false) ? 'checked' : '' }}>
                    <label for="multiplayer_only" class="text-gray-700 text-sm font-bold">Multiplayer only
                        <span class="text-gray-400 font-normal">(hidden from solo hunt selection)</span></label>
                    @error('multiplayer_only') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Boss encounters --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Boss Encounters
                <span class="text-gray-400 font-normal text-xs normal-case ml-1">(Wave 5)</span>
            </h2>
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Primary Boss</label>
                    <select name="boss_monster_id" class="w-full border rounded py-2 px-3">
                        <option value="">— no boss set —</option>
                        @foreach($bosses as $b)
                            <option value="{{ $b->id }}"
                                    {{ old('boss_monster_id', $quarry->boss_monster_id ?? '') == $b->id ? 'selected' : '' }}>
                                {{ $b->icon }} {{ $b->name }}
                                @if($b->quarry_title) ({{ $b->quarry_title }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('boss_monster_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Secondary Boss
                        <span class="text-gray-400 font-normal">(dual-boss encounter)</span></label>
                    <select name="boss_monster_id_2" class="w-full border rounded py-2 px-3">
                        <option value="">— single boss —</option>
                        @foreach($bosses as $b)
                            <option value="{{ $b->id }}"
                                    {{ old('boss_monster_id_2', $quarry->boss_monster_id_2 ?? '') == $b->id ? 'selected' : '' }}>
                                {{ $b->icon }} {{ $b->name }}
                                @if($b->quarry_title) ({{ $b->quarry_title }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('boss_monster_id_2') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Unique rewards --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Unique Rewards
                <span class="text-gray-400 font-normal text-xs normal-case ml-1">(one-time drops on completion)</span>
            </h2>
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Companion Pet Drop
                        <span class="text-gray-400 font-normal">(unique pet reward)</span></label>
                    <select name="loot_pet_slug" class="w-full border rounded py-2 px-3">
                        <option value="">— no pet drop —</option>
                        @foreach($pets as $p)
                            <option value="{{ $p->slug }}"
                                    {{ old('loot_pet_slug', $quarry->loot_pet_slug ?? '') === $p->slug ? 'selected' : '' }}>
                                {{ $p->icon }} {{ $p->name }} ({{ $p->slug }})
                            </option>
                        @endforeach
                    </select>
                    @error('loot_pet_slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Gear Drop
                        <span class="text-gray-400 font-normal">(unique gear reward)</span></label>
                    <select name="loot_gear_id" class="w-full border rounded py-2 px-3">
                        <option value="">— no gear drop —</option>
                        @foreach($gear as $g)
                            <option value="{{ $g->id }}"
                                    {{ old('loot_gear_id', $quarry->loot_gear_id ?? '') == $g->id ? 'selected' : '' }}>
                                {{ $g->name }} ({{ $g->slot }}){{ $g->is_gauntlet_loot ? ' ✦' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('loot_gear_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ isset($quarry) ? 'Update Quarry' : 'Create Quarry' }}
            </button>
            <a href="{{ route('dashboard.wildhunt.quarries.index') }}"
               class="text-blue-500 hover:underline">Cancel</a>
        </div>

    </form>

    {{-- Monsters list shown when editing --}}
    @if(isset($quarry) && isset($monsters) && $monsters->count())
        <h2 class="text-xl font-bold mt-8 mb-4">Monsters in this quarry</h2>
        <div class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Icon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">HP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Boss</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @foreach($monsters as $m)
                    <tr class="hover:bg-gray-50 {{ $m->is_boss ? 'bg-yellow-50' : '' }}">
                        <td class="px-6 py-3"><span class="text-xl">{{ $m->icon }}</span></td>
                        <td class="px-6 py-3 font-medium text-gray-900">{{ $m->name }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $m->level }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $m->hp }}</td>
                        <td class="px-6 py-3">
                            @if($m->is_boss)
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-semibold">Boss</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right">
                            <a href="{{ route('dashboard.wildhunt.monsters.edit', $m->id) }}"
                               class="bg-yellow-400 hover:bg-yellow-500 px-3 py-1 rounded text-xs font-semibold text-gray-900">Edit</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

@endsection
