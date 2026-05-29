@extends('dashboard')
@section('content')

    <div class="flex items-center gap-3 mb-1">
        <a href="{{ route('dashboard.wildhunt.pets.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Pets</a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('dashboard.wildhunt.pets.edit', $record->id) }}"
           class="text-gray-400 hover:text-gray-600 text-sm">{{ $record->name }}</a>
    </div>

    <h1 class="text-2xl font-bold mb-6">
        <span class="text-gray-500 font-normal">Icons for</span> {{ $record->icon }} {{ $record->name }}
    </h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Upload form --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-8">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-4">Upload New Icon</h2>
        <form method="POST"
              action="{{ route('dashboard.wildhunt.pets.icons.upload', $record->id) }}"
              enctype="multipart/form-data"
              class="flex items-end gap-4">
            @csrf
            <div class="flex-1">
                <label class="block text-gray-700 text-sm font-bold mb-2">PNG file
                    <span class="text-gray-400 font-normal">(max 512 KB — filename becomes the variant name, e.g. lynx_sand.png)</span>
                </label>
                <input type="file" name="icon" accept=".png"
                       class="w-full border border-gray-300 rounded py-2 px-3 text-sm">
            </div>
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                Upload
            </button>
        </form>
        <p class="text-gray-400 text-xs mt-3">
            Files are saved to <code class="bg-gray-100 px-1 rounded">resources/icons/pets/{{ $record->slug }}/</code>
            in the game project and become available immediately in the in-game icon picker.
        </p>
    </div>

    {{-- Existing icons --}}
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-4">
        Existing Icons ({{ count($files) }})
    </h2>

    @if(count($files) > 0)
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
            @foreach($files as $filename)
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 flex flex-col items-center gap-3">
                    <img src="{{ route('dashboard.wildhunt.pets.icons.preview', [$record->id, $filename]) }}"
                         alt="{{ $filename }}"
                         class="w-16 h-16 object-contain">
                    <span class="text-xs text-gray-500 font-mono text-center break-all">{{ $filename }}</span>
                    <form method="POST"
                          action="{{ route('dashboard.wildhunt.pets.icons.destroy', [$record->id, $filename]) }}"
                          onsubmit="return confirm('Delete {{ $filename }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="text-xs text-red-500 hover:text-red-700 font-semibold">Delete</button>
                    </form>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center text-gray-400">
            No PNG icons uploaded yet. Upload one above — once added, players can select it for their {{ $record->name }}.
        </div>
    @endif

@endsection
