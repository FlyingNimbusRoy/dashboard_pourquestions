@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">
        {{ isset($character) ? 'Edit Character' : 'Create New Character' }}
    </h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ isset($character) ? route('dashboard.characters.update', $character) : route('dashboard.characters.store') }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-6">
        @csrf
        @if(isset($character))
            @method('PUT')
        @endif

        <!-- Name -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
            <input type="text" name="name" value="{{ old('name', $character->name ?? '') }}" class="w-full border rounded py-2 px-3">
            @error('name') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <!-- URL / Character Image -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Character Image (URL or filename in img/characters)</label>
            <select name="url" id="character-url" class="w-full border rounded py-2 px-3 mb-2">
                <option value="">-- Select Character Image --</option>
                @foreach($images as $image)
                    <option value="{{ $image }}" {{ old('url', $character->url ?? '') == $image ? 'selected' : '' }}>
                        {{ $image }}
                    </option>
                @endforeach
            </select>
            <!-- <input type="text" name="url" id="character-url" value="{{ old('url', $character->url ?? '') }}" class="w-full border rounded py-2 px-3"> -->
            @error('url') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror

            <!-- Preview Container -->
            <div class="mt-2">
                <span class="text-gray-500 text-sm mb-1">Preview:</span>
                <div class="w-36 h-36 border rounded overflow-hidden flex items-center justify-center bg-gray-100">
                    <img id="character-preview" src="{{ old('url', isset($character) ? asset('img/characters/' . $character->url) : '') }}" alt="Character Preview" class="object-cover h-full">
                </div>
            </div>
        </div>

        <!-- Gamepack -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Gamepack</label>
            <select name="gamepack_id" class="w-full border rounded py-2 px-3">
                <option value="">-- Select --</option>
                @foreach($gamepacks as $gp)
                    <option value="{{ $gp->id }}" {{ old('gamepack_id', $character->gamepack_id ?? '') == $gp->id ? 'selected' : '' }}>
                        {{ $gp->name }}
                    </option>
                @endforeach
            </select>
            @error('gamepack_id') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <!-- Parent Character -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Parent Character</label>
            <select name="parent_character_id" class="w-full border rounded py-2 px-3">
                <option value="">-- None --</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_character_id', $character->parent_character_id ?? '') == $parent->id ? 'selected' : '' }}>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
            @error('parent_character_id') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <!-- Colors -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Primary Color</label>
                <input type="color" name="color_primary" value="{{ old('color_primary', $character->color_primary ?? '#6F1E51') }}">
                @error('color_primary') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Secondary Color</label>
                <input type="color" name="color_secondary" value="{{ old('color_secondary', $character->color_secondary ?? '#1B1464') }}">
                @error('color_secondary') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Muted Primary</label>
                <input type="color" name="color_muted_primary" value="{{ old('color_muted_primary', $character->color_muted_primary ?? '#ED4C67') }}">
                @error('color_muted_primary') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Muted Secondary</label>
                <input type="color" name="color_muted_secondary" value="{{ old('color_muted_secondary', $character->color_muted_secondary ?? '#B53471') }}">
                @error('color_muted_secondary') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Show on Homepage -->
        <div class="flex items-center gap-2">
            <input type="checkbox" name="show_on_homepage" value="1" {{ old('show_on_homepage', $character->show_on_homepage ?? 1) ? 'checked' : '' }}>
            <label class="text-gray-700 text-sm font-bold">Show on Homepage</label>
        </div>

        <div class="flex items-center justify-between mt-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ isset($character) ? 'Update Character' : 'Create Character' }}
            </button>
            <a href="{{ route('dashboard.characters.index') }}" class="text-blue-500 hover:underline">Cancel</a>
        </div>
    </form>

    <script>
        const urlInput = document.getElementById('character-url');
        const preview = document.getElementById('character-preview');

        urlInput.addEventListener('input', function() {
            let value = urlInput.value.trim();

            if(value) {
                // If it's just a filename, prepend the path
                if(!value.startsWith('http') && !value.startsWith('/')) {
                    value = '{{ asset("img/characters") }}/' + value;
                }
                preview.src = value;
            } else {
                preview.src = '';
            }
        });
    </script>
@endsection
