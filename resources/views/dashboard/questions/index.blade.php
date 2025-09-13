@extends('dashboard')

@section('content')
    <div class="space-y-6">

        <h1 class="text-3xl font-bold text-gray-800">Questions</h1>

        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 md:gap-6">
            <a href="{{ route('questions.create') }}"
               class="w-full px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700 transition text-center">
                Create New Question
            </a>
        </div>

        <!-- Create + Filters -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 md:gap-6">

            <form method="GET" action="{{ route('questions.index') }}"
                  class="flex flex-wrap gap-4 items-end bg-white p-4 rounded-lg shadow border border-gray-200 w-full overflow-x-auto">

                <div class="flex flex-col w-48">
                    <label class="text-sm font-medium mb-1 text-gray-700">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search questions..."
                           class="px-3 py-2 border rounded focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>

                <div class="flex flex-col w-32">
                    <label class="text-sm font-medium mb-1 text-gray-700">NSFW</label>
                    <select name="is_nsfw" class="px-3 py-2 border rounded" onchange="this.form.submit()">
                        <option value="">Any</option>
                        <option value="1" {{ request('is_nsfw') === '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ request('is_nsfw') === '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <!-- Random -->
                <div class="flex flex-col w-32">
                    <label class="text-sm font-medium mb-1 text-gray-700">Random</label>
                    <select name="is_random" class="px-3 py-2 border rounded" onchange="this.form.submit()">
                        <option value="">Any</option>
                        <option value="1" {{ request('is_random') === '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ request('is_random') === '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <!-- Category -->
                <div class="flex flex-col w-40">
                    <label class="text-sm font-medium mb-1 text-gray-700">Category</label>
                    <select name="category_id" class="px-3 py-2 border rounded" onchange="this.form.submit()">
                        <option value="">Any</option>
                        @foreach(\App\Models\Category::all() as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Gamepack -->
                <div class="flex flex-col w-40">
                    <label class="text-sm font-medium mb-1 text-gray-700">Gamepack</label>
                    <select name="gamepack_id" class="px-3 py-2 border rounded" onchange="this.form.submit()">
                        <option value="">Any</option>
                        @foreach(\App\Models\Gamepack::all() as $gamepack)
                            <option value="{{ $gamepack->id }}" {{ request('gamepack_id') == $gamepack->id ? 'selected' : '' }}>
                                {{ $gamepack->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Difficulty -->
                <div class="flex flex-col w-32">
                    <label class="text-sm font-medium mb-1 text-gray-700">Difficulty</label>
                    <select name="difficulty" class="px-3 py-2 border rounded" onchange="this.form.submit()">
                        <option value="">Any</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ request('difficulty') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="flex flex-col justify-end">
                    <a href="{{ route('questions.index') }}"
                       class="px-4 py-2 bg-gray-300 rounded shadow hover:bg-gray-400 transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Questions Table -->
        <div class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-50">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">ID</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Question</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Difficulty</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($questions as $question)
                    <tr class="hover:bg-blue-50 transition">
                        <td class="px-6 py-3 whitespace-nowrap">{{ $question->id }}</td>
                        <td class="px-6 py-3 whitespace-wrap">{{ $question->vraag }}</td>
                        <td class="px-6 py-3 whitespace-nowrap">{{ $question->difficulty }}</td>
                        <td class="px-6 py-3 whitespace-nowrap space-x-3">
                            <a href="{{ route('questions.edit', $question) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('questions.destroy', $question) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No questions found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $questions->links() }}
        </div>

    </div>
@endsection
