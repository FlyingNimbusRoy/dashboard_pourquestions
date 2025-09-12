@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Edit Question</h1>

    <form action="{{ route('questions.update', $question) }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="vraag">Question</label>
            <input type="text" name="vraag" id="vraag" value="{{ old('vraag', $question->vraag) }}"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            @error('vraag') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="difficulty">Difficulty</label>
            <input type="number" name="difficulty" id="difficulty" value="{{ old('difficulty', $question->difficulty) }}"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            @error('difficulty') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Update
            </button>
            <a href="{{ route('questions.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                Cancel
            </a>
        </div>
    </form>
@endsection
