@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Comments</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('dashboard.comments.create') }}"
           class="px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700 dashboard__cta">
            Add Comment
        </a>
    </div>

    <div class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comment</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
        </tr>
        </thead>

        <tbody>
        @forelse($comments as $comment)
            <tr class="border-b">
                <td class="px-6 py-3">{{ $comment->id }}</td>
                <td class="px-6 py-3">{{ $comment->comment }}</td>
                <td class="px-6 py-3">{{ $comment->created_at->diffForHumans() }}</td>
                <td class="px-6 py-3 flex gap-2">
                    <a href="{{ route('dashboard.comments.edit', $comment) }}"
                       class="bg-yellow-400 px-2 py-1 rounded hover:bg-yellow-500 text-white">Edit</a>
                    <form action="{{ route('dashboard.comments.destroy', $comment) }}" method="POST"
                          onsubmit="return confirm('Delete this comment?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="bg-red-500 px-2 py-1 rounded hover:bg-red-700 text-white">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="py-4 px-4 text-center text-gray-500">
                    No comments found.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="mt-4">
        {{ $comments->links() }}
    </div>
@endsection
