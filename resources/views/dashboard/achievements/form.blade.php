@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">
        {{ isset($achievement) ? 'Edit Achievement' : 'Create New Achievement' }}
    </h1>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ isset($achievement) ? route('dashboard.achievements.update', $achievement) : route('dashboard.achievements.store') }}"
          method="POST"
          class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-6">
        @csrf
        @if(isset($achievement))
            @method('PUT')
        @endif

        {{-- Name --}}
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
            <input type="text" name="name"
                   value="{{ old('name', $achievement->name ?? '') }}"
                   class="w-full border rounded py-2 px-3">
            @error('name') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
            <textarea name="description"
                      class="w-full border rounded py-2 px-3"
                      rows="3"
                      placeholder="Shown on the user's profile...">{{ old('description', $achievement->description ?? '') }}</textarea>
            @error('description') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        {{-- Image --}}
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Badge Image</label>
            <p class="text-gray-500 text-xs mb-2">Enter the filename of an image uploaded via
                <a href="{{ route('dashboard.achievements.upload') }}" class="text-blue-500 underline" target="_blank">Manage Badge Images</a>.
            </p>
            <input type="text" name="image"
                   value="{{ old('image', $achievement->image ?? '') }}"
                   class="w-full border rounded py-2 px-3"
                   placeholder="e.g. badge-10-games.png">
            @if(isset($achievement) && $achievement->image)
                <div class="mt-2 flex items-center gap-3">
                    <img src="{{ asset('img/achievements/' . $achievement->image) }}"
                         alt="Badge" class="w-12 h-12 object-contain border rounded">
                    <span class="text-xs text-gray-500">{{ $achievement->image }}</span>
                </div>
            @endif
            @error('image') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        {{-- Criteria Type --}}
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Criteria Type</label>
            <select name="criteria_type" class="w-full border rounded py-2 px-3">
                @foreach(\App\Models\Achievement::CRITERIA_TYPES as $value => $label)
                    <option value="{{ $value }}"
                            {{ old('criteria_type', $achievement->criteria_type ?? '') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('criteria_type') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        {{-- Criteria Value --}}
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Threshold Value</label>
            <p class="text-gray-500 text-xs mb-2">The number the player must reach to unlock this achievement.</p>
            <input type="number" name="criteria_value" min="1"
                   value="{{ old('criteria_value', $achievement->criteria_value ?? '') }}"
                   class="w-full border rounded py-2 px-3"
                   placeholder="e.g. 10">
            @error('criteria_value') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Save
            </button>
            <a href="{{ route('dashboard.achievements.index') }}" class="text-blue-500 hover:underline">Cancel</a>
        </div>
    </form>
@endsection