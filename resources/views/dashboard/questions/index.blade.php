@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Questions</h1>

    <!-- Filter & Create Button -->
    <div class="flex flex-wrap justify-between items-end mb-4 gap-4">

        <!-- Create Question -->
        <a href="{{ route('questions.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded">
            Create New Question
        </a>

        <!-- Filters Form -->
        <form method="GET" action="{{ route('questions.index') }}" class="flex flex-wrap gap-2 items-end">

            <!-- Search -->
            <div>
                <label class="block text-sm font-medium">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search questions..."
                       class="px-3 py-2 border rounded focus:ring focus:outline-none">
            </div>

            <!-- NSFW -->
            <div>
                <label class="block text-sm font-medium">NSFW</label>
                <select name="is_nsfw" class="px-3 py-2 border rounded">
                    <option value="">Any</option>
                    <option value="1" {{ request('is_nsfw') === '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ request('is_nsfw') === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <!-- Random -->
            <div>
                <label class="block text-sm font-medium">Random</label>
                <select name="is_random" class="px-3 py-2 border rounded">
                    <option value="">Any</option>
                    <option value="1" {{ request('is_random') === '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ request('is_random') === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <!-- Category -->
{{--            <div>--}}
{{--                <label class="block text-sm font-medium">Category</label>--}}
{{--                <select name="category_id" class="px-3 py-2 border rounded">--}}
{{--                    <option value="">Any</option>--}}
{{--                    @foreach(\App\Models\Category::all() as $category)--}}
{{--                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>--}}
{{--                            {{ $category->name }}--}}
{{--                        </option>--}}
{{--                    @endforeach--}}
{{--                </select>--}}
{{--            </div>--}}

            <!-- Gamepack -->
{{--            <div>--}}
{{--                <label class="block text-sm font-medium">Gamepack</label>--}}
{{--                <select name="gamepack_id" class="px-3 py-2 border rounded">--}}
{{--                    <option value="">Any</option>--}}
{{--                    @foreach(\App\Models\Gamepack::all() as $gamepack)--}}
{{--                        <option value="{{ $gamepack->id }}" {{ request('gamepack_id') == $gamepack->id ? 'selected' : '' }}>--}}
{{--                            {{ $gamepack->name }}--}}
{{--                        </option>--}}
{{--                    @endforeach--}}
{{--                </select>--}}
{{--            </div>--}}

            <!-- Difficulty -->
            <div>
                <label class="block text-sm font-medium">Difficulty</label>
                <select name="difficulty" class="px-3 py-2 border rounded">
                    <option value="">Any</option>
                    @for($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" {{ request('difficulty') == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>

            <!-- Submit -->
            <div>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Filter</button>
                <a href="{{ route('questions.index') }}" class="px-4 py-2 bg-gray-300 rounded ml-2">Reset</a>
            </div>
        </form>
    </div>

    <!-- Questions Table -->
    <table class="min-w-full border border-gray-200 bg-white">
        <thead class="bg-gray-100">
        <tr>
            <th class="px-4 py-2 border text-left">ID</th>
            <th class="px-4 py-2 border text-left">Question</th>
            <th class="px-4 py-2 border text-left">Difficulty</th>
            <th class="px-4 py-2 border text-left">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($questions as $question)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 border">{{ $question->id }}</td>
                <td class="px-4 py-2 border">{{ $question->vraag }}</td>
                <td class="px-4 py-2 border">{{ $question->difficulty }}</td>
                <td class="px-4 py-2 border space-x-2">
                    <a href="{{ route('questions.edit', $question) }}" class="text-blue-600 hover:underline">Edit</a>
                    <form action="{{ route('questions.destroy', $question) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline"
                                onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-4 py-2 text-center">No questions found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $questions->links() }}
    </div>
@endsection
