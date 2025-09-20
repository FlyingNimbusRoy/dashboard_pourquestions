@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">
        {{ isset($comment) ? 'Edit Comment' : 'Add Comment' }}
    </h1>

    <form action="{{ isset($comment) ? route('dashboard.comments.update', $comment) : route('dashboard.comments.store') }}"
          method="POST"
          class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-6">
        @csrf
        @if(isset($comment))
            @method('PUT')
        @endif

        <!-- Comment -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Comment</label>
            <input type="text" name="comment"
                   value="{{ old('comment', $comment->comment ?? '') }}"
                   class="w-full border rounded py-2 px-3">
            @error('comment') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ isset($comment) ? 'Update' : 'Save' }}
            </button>
            <a href="{{ route('dashboard.comments.index') }}"
               class="text-blue-500 hover:underline">Cancel</a>
        </div>
    </form>
@endsection
