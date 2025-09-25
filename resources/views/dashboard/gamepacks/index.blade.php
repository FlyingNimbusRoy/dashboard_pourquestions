@extends('dashboard')

@section('content')
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-800">Gamepacks</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 md:gap-6 mb-4">
            <a href="{{ route('dashboard.gamepacks.create') }}"
               class="w-full px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700 transition text-center">
                Create New Gamepack
            </a>
        </div>

        <div class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Icon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Color</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cover Art</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
                </thead>

                <tbody>
                @foreach($gamepacks as $gp)
                    <tr class="border-b">
                        <td class="px-6 py-3">{{ $gp->id }}</td>
                        <td class="px-6 py-3">{{ $gp->name }}</td>
                        <td class="px-6 py-3">{{ $gp->description }}</td>
                        <td class="px-6 py-3"><i class="{{ $gp->icon }}"></i> {{ $gp->icon }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-block w-6 h-6 rounded"
                                  style="background-color: {{ $gp->color }}"></span>
                            {{ $gp->color }}
                        </td>
                        <td class="px-6 py-3">${{ number_format($gp->price, 2) }}</td>
                        <td class="px-6 py-3"><img src="{{ $gp->url_coverart }}" class="w-12 h-12 object-cover"
                                                   alt="Cover Art"></td>
                        <td class="px-6 py-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('dashboard.gamepacks.edit', $gp) }}"
                                   class="bg-yellow-400 px-2 py-1 rounded hover:bg-yellow-500 text-blue-600">Edit</a>
                                <form action="{{ route('dashboard.gamepacks.destroy', $gp) }}" method="POST"
                                      onsubmit="return confirm('Delete this gamepack?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-500 text-red-600 px-2 py-1 rounded hover:bg-red-700">Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $gamepacks->links() }}
    </div>
@endsection
