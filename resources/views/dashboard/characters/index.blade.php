@extends('dashboard')

@section('content')
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-800">Characters</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 md:gap-6 mb-4">
            <a href="{{ route('dashboard.characters.create') }}" class="w-full px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700 transition text-center">
                Create New Character
            </a>
        </div>

        <table class="min-w-full bg-white border rounded">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>URL</th>
                <th>Gamepack</th>
                <th>Primary Color</th>
                <th>Secondary Color</th>
                <th>Muted Primary</th>
                <th>Muted Secondary</th>
                <th>Show on Homepage</th>
                <th>Parent Character</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($characters as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->name }}</td>
                    <td><a href="{{ $c->url }}" target="_blank">{{ $c->url }}</a></td>
                    <td>{{ $c->gamepack?->name }}</td>
                    <td><span class="inline-block w-6 h-6 rounded"
                              style="background-color: {{ $c->color_primary }}"></span> {{ $c->color_primary }}</td>
                    <td><span class="inline-block w-6 h-6 rounded"
                              style="background-color: {{ $c->color_secondary }}"></span> {{ $c->color_secondary }}</td>
                    <td><span class="inline-block w-6 h-6 rounded"
                              style="background-color: {{ $c->color_muted_primary }}"></span> {{ $c->color_muted_primary }}
                    </td>
                    <td><span class="inline-block w-6 h-6 rounded"
                              style="background-color: {{ $c->color_muted_secondary }}"></span> {{ $c->color_muted_secondary }}
                    </td>
                    <td>{{ $c->show_on_homepage ? 'Yes' : 'No' }}</td>
                    <td>{{ $c->parent?->name }}</td>
                    <td class="whitespace-nowrap flex gap-2">
                        <a href="{{ route('dashboard.characters.edit', $c) }}"
                           class="bg-yellow-400 px-2 py-1 rounded hover:bg-yellow-500 text-blue-600">Edit</a>
                        <form action="{{ route('dashboard.characters.destroy', $c) }}" method="POST"
                              onsubmit="return confirm('Delete this character?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 px-2 py-1 rounded hover:bg-red-700 text-red-600">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $characters->links() }}
    </div>
@endsection
