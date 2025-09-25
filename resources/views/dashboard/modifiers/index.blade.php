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

    <div class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gamepack</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
            </thead>

            <tbody>
            @forelse($modifiers as $modifier)
                <tr class="border-b">
                    <td class="px-6 py-3 ">{{ $modifier->id }}</td>
                    <td class="px-6 py-3 flex items-center gap-2">
                        {!! $modifier->fa_icon !!}
                        <span>{{ $modifier->name }}</span>
                    </td>
                    <td class="px-6 py-3">
                        {{ $modifier->gamepack?->name ?? '—' }}
                    </td>
                    <td class="px-6 py-3">
                        @if($modifier->is_active)
                            <span class="px-2 py-1 bg-green-200 text-green-800 rounded text-xs">Active</span>
                        @else
                            <span class="px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-3">{{ $modifier->created_at->diffForHumans() }}</td>
                    <td class="px-6 py-3 flex gap-2">
                        <a href="{{ route('dashboard.modifiers.edit', $modifier) }}"
                           class="bg-yellow-400 px-2 py-1 rounded hover:bg-yellow-500 text-white">Edit</a>
                        <form action="{{ route('dashboard.modifiers.destroy', $modifier) }}" method="POST"
                              onsubmit="return confirm('Delete this modifier?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="bg-red-500 px-2 py-1 rounded hover:bg-red-700 text-white">Delete
                            </button>
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
    </div>
    <div class="mt-4">
        {{ $modifiers->links() }}
    </div>
@endsection
