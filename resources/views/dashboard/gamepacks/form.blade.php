@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">
        {{ isset($gamepack) ? 'Edit Gamepack' : 'Create New Gamepack' }}
    </h1>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ isset($gamepack) ? route('dashboard.gamepacks.update', $gamepack) : route('dashboard.gamepacks.store') }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-6">
        @csrf
        @if(isset($gamepack))
            @method('PUT')
        @endif

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
            <input type="text" name="name" value="{{ old('name', $gamepack->name ?? '') }}" class="w-full border rounded py-2 px-3">
            @error('name') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
            <input type="text" name="description" value="{{ old('description', $gamepack->description ?? '') }}" class="w-full border rounded py-2 px-3">
            @error('description') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Icon</label>
            <input type="text" name="icon" value="{{ old('icon', $gamepack->icon ?? '') }}" class="w-full border rounded py-2 px-3">
            @if(isset($gamepack) && $gamepack->icon)
                <p class="mt-2">Preview: <i class="{{ $gamepack->icon }}"></i></p>
            @endif
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Color</label>
            <input type="color" name="color" value="{{ old('color', $gamepack->color ?? '#ffffff') }}" class="w-16 h-10 border rounded px-2 py-1">
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Price</label>
            <input type="number" name="price" step="0.01" value="{{ old('price', $gamepack->price ?? 0) }}" class="w-full border rounded py-2 px-3">
            @error('price') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Cover Art URL</label>
            <input type="text" name="url_coverart" value="{{ old('url_coverart', $gamepack->url_coverart ?? '') }}" class="w-full border rounded py-2 px-3">
            @if(isset($gamepack) && $gamepack->url_coverart)
                <p class="mt-2"><img src="{{ $gamepack->url_coverart }}" class="w-24 h-24 object-cover" alt="Cover Art"></p>
            @endif
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Save
            </button>
            <a href="{{ route('dashboard.gamepacks.index') }}" class="text-blue-500 hover:underline">Cancel</a>
        </div>
    </form>
@endsection
