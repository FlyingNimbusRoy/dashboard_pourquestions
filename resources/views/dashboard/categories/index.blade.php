@extends('dashboard')

@section('content')
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-800">Categories</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 md:gap-6">
            <a href="{{ route('dashboard.categories.create') }}"
               class="w-full px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700 transition text-center">
                Create New Category
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
                <th class="py-2 px-4 border-b">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($categories as $category)
                <tr class="border-b">
                    <td class="py-2 px-4">{{ $category->id }}</td>
                    <td class="py-2 px-4">{{ $category->name }}</td>
                    <td class="py-2 px-4">{{ $category->description }}</td>
                    <td class="py-2 px-4">{{ $category->icon }}</td>
                    <td class="py-2 px-4">
                        <span class="inline-block w-6 h-6 rounded" style="background-color: {{ $category->color }}"></span>
                        {{ $category->color }}
                    </td>
                    <td class="py-2 px-4 flex gap-2">
                        <a href="{{ route('dashboard.categories.edit', $category) }}"
                           class="bg-yellow-400 px-2 py-1 rounded text-blue-600 hover:bg-yellow-500">Edit</a>
                        <form action="{{ route('dashboard.categories.destroy', $category) }}" method="POST"
                              onsubmit="return confirm('Delete this category?');">
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

        {{ $categories->links() }}
    </div>
@endsection
