@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">
        {{ isset($modifier) ? 'Edit Modifier' : 'Add Modifier' }}
    </h1>

    <form action="{{ isset($modifier) ? route('dashboard.modifiers.update', $modifier) : route('dashboard.modifiers.store') }}"
          method="POST"
          class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-6">
        @csrf
        @if(isset($modifier))
            @method('PUT')
        @endif

        <!-- Name -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
            <input type="text" name="name"
                   value="{{ old('name', $modifier->name ?? '') }}"
                   class="w-full border rounded py-2 px-3">
            @error('name') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <!-- Description -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
            <textarea name="description" rows="3"
                      class="w-full border rounded py-2 px-3">{{ old('description', $modifier->description ?? '') }}</textarea>
            @error('description') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <!-- Modifier Icon -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Modifier Icon</label>
            <select name="icon" class="w-full border rounded py-2 px-3">
                <option value="">— None —</option>
                @php
                    $modifierImages = File::exists(public_path('img/modifiers'))
                        ? File::files(public_path('img/modifiers'))
                        : [];
                @endphp
                @foreach($modifierImages as $image)
                    @php $filename = $image->getFilename(); @endphp
                    <option value="{{ $filename }}"
                        {{ old('icon', $modifier->icon ?? '') === $filename ? 'selected' : '' }}>
                        {{ $filename }}
                    </option>
                @endforeach
            </select>

            <!-- Show preview of selected icon if editing -->
            @if(isset($modifier) && !empty($modifier->icon))
                <div class="mt-2 w-16 h-16 border rounded overflow-hidden">
                    <img src="{{ asset('img/modifiers/' . $modifier->icon) }}"
                         alt="Modifier Icon" class="w-full h-full object-contain">
                </div>
            @endif

            @error('icon') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>


        <!-- Turnbased -->
        <div class="flex items-center gap-2">
            <input type="checkbox" name="turnbased" value="1"
                {{ old('turnbased', $modifier->turnbased ?? false) ? 'checked' : '' }}>
            <label class="text-gray-700 text-sm font-bold">Turnbased</label>
            @error('turnbased') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <!-- Effects (JSON) -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Effects (JSON)</label>
            <textarea name="effects" rows="5"
                      class="w-full border rounded py-2 px-3"
                      placeholder='{"onWrongAnswer": {"redirect_shot": "random"}}'>{{ old('effects', isset($modifier->effects) && is_array($modifier->effects) ? json_encode($modifier->effects, JSON_PRETTY_PRINT) : ($modifier->effects ?? '')) }}</textarea>
            @error('effects') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <!-- Gamepack -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Gamepack</label>
            <select name="coupled_gamepack_id" class="w-full border rounded py-2 px-3">
                <option value="">— None —</option>
                @foreach($gamepacks as $gamepack)
                    <option value="{{ $gamepack->id }}"
                        {{ old('coupled_gamepack_id', $modifier->coupled_gamepack_id ?? null) == $gamepack->id ? 'selected' : '' }}>
                        {{ $gamepack->name }}
                    </option>
                @endforeach
            </select>
            @error('coupled_gamepack_id') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <!-- Active -->
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1"
                {{ old('is_active', $modifier->is_active ?? true) ? 'checked' : '' }}>
            <label class="text-gray-700 text-sm font-bold">Active</label>
            @error('is_active') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ isset($modifier) ? 'Update' : 'Save' }}
            </button>
            <a href="{{ route('dashboard.modifiers.index') }}"
               class="text-blue-500 hover:underline">Cancel</a>
        </div>
    </form>
@endsection
