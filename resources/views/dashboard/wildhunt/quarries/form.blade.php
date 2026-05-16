@extends('dashboard')
@section('content')

    <h1 class="text-2xl font-bold mb-4">
        {{ isset($quarry) ? 'Edit Quarry' : 'New Quarry' }}
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
          class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-6">
        @csrf
        @if(isset($quarry)) @method('PUT') @endif

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Title</label>
            <input type="text" name="title" value="{{ old('title', $quarry->title ?? '') }}"
                   class="w-full border rounded py-2 px-3">
            @error('title') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Type slug <span class="text-gray-400 font-normal">(e.g. mount_vorfang)</span></label>
            <input type="text" name="type" value="{{ old('type', $quarry->type ?? '') }}"
                   class="w-full border rounded py-2 px-3 font-mono">
            @error('type') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Tier</label>
                <select name="tier" class="w-full border rounded py-2 px-3">
                    @foreach(range(1, 6) as $t)
                        <option value="{{ $t }}" {{ old('tier', $quarry->tier ?? 1) == $t ? 'selected' : '' }}>
                            Tier {{ $t }}
                        </option>
                    @endforeach
                </select>
                @error('tier') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Min level</label>
                <input type="number" name="min_player_level" value="{{ old('min_player_level', $quarry->min_player_level ?? 1) }}" min="1"
                       class="w-full border rounded py-2 px-3">
                @error('min_level') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
            <textarea name="description" rows="4"
                      class="w-full border rounded py-2 px-3">{{ old('description', $quarry->description ?? '') }}</textarea>
            @error('description') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ isset($quarry) ? 'Update Quarry' : 'Create Quarry' }}
            </button>
            <a href="{{ route('dashboard.wildhunt.quarries.index') }}"
               class="text-blue-500 hover:underline">Cancel</a>
        </div>

    </form>

    {{-- Monsters list shown when editing an existing quarry --}}
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
                        <td class="px-6 py-3">
                            @if(str_starts_with($m->icon, 'http'))
                                <img src="{{ $m->icon }}" class="w-8 h-8 object-contain">
                            @elseif(str_starts_with($m->icon, '/') || str_contains($m->icon, '.'))
                                <img src="https://spire.pourquestions.com/build/assets/{{ $m->icon }}" class="w-8 h-8 object-contain">
                            @else
                                <span class="text-xl">{{ $m->icon }}</span>
                            @endif
                        </td>
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
