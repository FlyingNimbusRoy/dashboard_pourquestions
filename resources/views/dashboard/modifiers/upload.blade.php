@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Upload Modifier Image</h1>

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

    <form action="{{ route('dashboard.modifiers.upload.store') }}" method="POST" enctype="multipart/form-data" class="mb-6">
        @csrf
        <div class="flex flex-col gap-2">
            <input type="file" name="image" required class="border rounded p-1">
            @error('image')
            <p class="text-red-500 text-xs">{{ $message }}</p>
            @enderror
            <button type="submit" class="w-full dashboard__cta text-white px-4 py-2 rounded hover:bg-blue-700 w-32">
                Upload
            </button>
        </div>
    </form>

    <h2 class="text-xl font-semibold mb-2">Current Modifier Images</h2>
    <div class="flex flex-wrap gap-3">
        @foreach($images as $image)
            <div class="w-32 flex flex-col items-center">
                <div class="w-32 h-32 border rounded-lg shadow overflow-hidden bg-gray-100">
                    <img src="{{ asset('img/modifiers/' . $image->getFilename()) }}"
                         alt="Modifier"
                         class="w-full h-full object-cover">
                </div>

                <!-- Delete Button -->
                <form action="{{ route('dashboard.modifiers.upload.destroy', $image->getFilename()) }}"
                      method="POST"
                      class="mt-2 w-full">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full bg-red-600 text-white text-xs px-2 py-1 rounded hover:bg-red-700">
                        Delete
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endsection
