@extends('dashboard')

@section('content')
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-800">Achievements</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 md:gap-6 mb-4">
            <a href="{{ route('dashboard.achievements.create') }}"
               class="w-full px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700 transition text-center">
                Create New Achievement
            </a>
            <a href="{{ route('dashboard.achievements.upload') }}"
               class="w-full px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700 transition text-center">
                Manage Badge Images
            </a>
        </div>

        <div class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Badge</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Criteria</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Threshold</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unlocked By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @foreach($achievements as $achievement)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $achievement->id }}</td>
                        <td class="px-6 py-4">
                            <img src="{{ asset('' . $achievement->image) }}"
                                 alt="{{ $achievement->name }}"
                                 class="w-10 h-10 object-contain">
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $achievement->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $achievement->description }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $achievement->criteria_label }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $achievement->criteria_value }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $achievement->users()->count() }}</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('dashboard.achievements.edit', $achievement) }}"
                                   class="bg-yellow-400 px-2 py-1 rounded hover:bg-yellow-500 text-blue-600">Edit</a>
                                <form action="{{ route('dashboard.achievements.destroy', $achievement) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete this achievement?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $achievements->links() }}
    </div>
@endsection