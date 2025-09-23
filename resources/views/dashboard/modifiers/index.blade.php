@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Modifiers</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('dashboard.modifiers.create') }}"
           class="px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700">
            Add Modifier
        </a>
    </div>

    <table class="min-w-full bg-white border fi-align-left rounded">
        <thead>
        <tr>
            <th class="py-2 px-4 border-b">ID</th>
            <th class="py-2 px-4 border-b">Name</th>
            <th class="py-2 px-4 border-b">Gamepack</th>
            <th class="py-2 px-4 border-b">Active</th>
            <th class="py-2 px-4 border-b">Created</th>
            <th class="py-2 px-4 border-b">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($modifiers as $modifier)
            <tr class="border-b">
                <td class="py-2 px-4">{{ $modifier->id }}</td>
                <td class="py-2 px-4 flex items-center gap-2">
                    {!! $modifier->fa_icon !!}
                    <span>{{ $modifier->name }}</span>
                </td>
                <td class="py-2 px-4">
                    {{ $modifier->gamepack?->name ?? '—' }}
                </td>
                <td class="py-2 px-4">
                    @if($modifier->is_active)
                        <span class="px-2 py-1 bg-green-200 text-green-800 rounded text-xs">Active</span>
                    @else
                        <span class="px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs">Inactive</span>
                    @endif
                </td>
                <td class="py-2 px-4">{{ $modifier->created_at->diffForHumans() }}</td>
                <td class="py-2 px-4 flex gap-2">
                    <a href="{{ route('dashboard.modifiers.edit', $modifier) }}"
                       class="bg-yellow-400 px-2 py-1 rounded hover:bg-yellow-500 text-white">Edit</a>
                    <form action="{{ route('dashboard.modifiers.destroy', $modifier) }}" method="POST"
                          onsubmit="return confirm('Delete this modifier?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="bg-red-500 px-2 py-1 rounded hover:bg-red-700 text-white">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="py-4 px-4 text-center text-gray-500">
                    No modifiers found.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $modifiers->links() }}
    </div>
@endsection
