@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Questions</h1>

    <a href="{{ route('questions.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded mb-4 inline-block">
        Create New Question
    </a>

    <table class="min-w-full bg-white border mt-4">
        <thead>
        <tr>
            <th class="px-4 py-2 border">ID</th>
            <th class="px-4 py-2 border">Question</th>
            <th class="px-4 py-2 border">Difficulty</th>
            <th class="px-4 py-2 border">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($questions as $question)
            <tr>
                <td class="px-4 py-2 border">{{ $question->id }}</td>
                <td class="px-4 py-2 border">{{ $question->vraag }}</td>
                <td class="px-4 py-2 border">{{ $question->difficulty }}</td>
                <td class="px-4 py-2 border">
                    <a href="{{ route('questions.edit', $question) }}" class="text-blue-600 hover:underline">Edit</a>
                    <form action="{{ route('questions.destroy', $question) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline ml-2"
                                onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-4 py-2 border text-center">No questions found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
