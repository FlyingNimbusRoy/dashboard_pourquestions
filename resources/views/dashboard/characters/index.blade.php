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

        <div class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">URL</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Gamepack</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Colors</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Show on Homepage</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Parent Character</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Actions</th>
                </tr>
                </thead>


                <tbody class="divide-y divide-gray-200">
                @foreach($characters as $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $c->id }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $c->name }}</td>
                        <td class="px-6 py-4 text-sm text-blue-600 underline">
                            <a href="{{ $c->url }}" target="_blank">{{ $c->url }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $c->gamepack?->name }}</td>

                        <td class="px-6 py-4 text-sm text-gray-700 flex items-center gap-2">
            <span class="inline-block w-5 h-5 rounded"
                  style="background-color: {{ $c->color_primary }}"></span>
                            {{ $c->color_primary }}
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-700 flex items-center gap-2">
            <span class="inline-block w-5 h-5 rounded"
                  style="background-color: {{ $c->color_secondary }}"></span>
                            {{ $c->color_secondary }}
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-700 flex items-center gap-2">
            <span class="inline-block w-5 h-5 rounded"
                  style="background-color: {{ $c->color_muted_primary }}"></span>
                            {{ $c->color_muted_primary }}
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-700 flex items-center gap-2">
            <span class="inline-block w-5 h-5 rounded"
                  style="background-color: {{ $c->color_muted_secondary }}"></span>
                            {{ $c->color_muted_secondary }}
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-700">
                            {{ $c->show_on_homepage ? 'Yes' : 'No' }}
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-700">{{ $c->parent?->name }}</td>

                        <td class="px-6 py-4 whitespace-nowrap text-right flex gap-2">
                            <a href="{{ route('dashboard.characters.edit', $c) }}"
                               class="bg-yellow-400 hover:bg-yellow-500 px-3 py-1 rounded text-xs font-semibold text-gray-900">
                                Edit
                            </a>
                            <form action="{{ route('dashboard.characters.destroy', $c) }}" method="POST"
                                  onsubmit="return confirm('Delete this character?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-xs font-semibold text-white">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>

        {{ $characters->links() }}
    </div>
@endsection
