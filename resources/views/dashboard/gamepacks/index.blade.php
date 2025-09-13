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
        <a href="{{ route('dashboard.gamepacks.create') }}" class="w-full px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700 transition text-center">
            Create New Gamepack
        </a>
    </div>

    <table class="min-w-full bg-white border rounded">
        <thead>
        <tr>
            <th class="py-2 px-4 border-b">ID</th>
            <th class="py-2 px-4 border-b">Name</th>
            <th class="py-2 px-4 border-b">Description</th>
            <th class="py-2 px-4 border-b">Icon</th>
            <th class="py-2 px-4 border-b">Color</th>
            <th class="py-2 px-4 border-b">Price</th>
            <th class="py-2 px-4 border-b">Cover Art</th>
            <th class="py-2 px-4 border-b">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($gamepacks as $gp)
            <tr class="border-b">
                <td class="py-2 px-4">{{ $gp->id }}</td>
                <td class="py-2 px-4">{{ $gp->name }}</td>
                <td class="py-2 px-4">{{ $gp->description }}</td>
                <td class="py-2 px-4"><i class="{{ $gp->icon }}"></i> {{ $gp->icon }}</td>
                <td class="py-2 px-4">
                    <span class="inline-block w-6 h-6 rounded" style="background-color: {{ $gp->color }}"></span>
                    {{ $gp->color }}
                </td>
                <td class="py-2 px-4">${{ number_format($gp->price, 2) }}</td>
                <td class="py-2 px-4"><img src="{{ $gp->url_coverart }}" class="w-12 h-12 object-cover" alt="Cover Art"></td>
                <td class="py-2 px-4">
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('dashboard.gamepacks.edit', $gp) }}" class="bg-yellow-400 px-2 py-1 rounded hover:bg-yellow-500 text-blue-600">Edit</a>
                        <form action="{{ route('dashboard.gamepacks.destroy', $gp) }}" method="POST" onsubmit="return confirm('Delete this gamepack?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 text-red-600 px-2 py-1 rounded hover:bg-red-700">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $gamepacks->links() }}
    </div>
@endsection
