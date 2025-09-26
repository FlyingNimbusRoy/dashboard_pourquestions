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

        <div class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Icon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Color</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
                </thead>

                <tbody>
                @foreach($categories as $category)
                    <tr class="border-b">
                        <td class="px-6 py-3">{{ $category->id }}</td>
                        <td class="px-6 py-3">{{ $category->name }}</td>
                        <td class="px-6 py-3">{{ $category->description }}</td>
                        <td class="px-6 py-3">{{ $category->icon }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-block w-6 h-6 rounded"
                                  style="background-color: {{ $category->color }}"></span>
                            {{ $category->color }}
                        </td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('dashboard.categories.edit', $category) }}"
                               class="bg-yellow-400 px-2 py-1 rounded text-blue-600 hover:bg-yellow-500">Edit</a>
                            <form action="{{ route('dashboard.categories.destroy', $category) }}" method="POST"
                                  onsubmit="return confirm('Delete this category?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-500 px-2 py-1 rounded hover:bg-red-700 text-red-600">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $categories->links() }}
    </div>
@endsection
