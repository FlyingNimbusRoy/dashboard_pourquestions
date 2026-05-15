@extends('dashboard')
@section('content')

    <h1 class="text-2xl font-bold mb-4">Quarries</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('dashboard.wildhunt.quarries.create') }}"
           class="px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700">
            + New Quarry
        </a>
    </div>

    @php $tiers = $quarries->groupBy('tier')->sortKeys(); @endphp

    @foreach($tiers as $tier => $tierQuarries)
        <h2 class="text-sm font-semibold uppercase tracking-widest text-gray-500 mt-6 mb-2">Tier {{ $tier }}</h2>
        <div class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white mb-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Min level</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @foreach($tierQuarries as $quarry)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 font-medium text-gray-900">{{ $quarry->title }}</td>
                        <td class="px-6 py-3 text-gray-500 font-mono text-xs">{{ $quarry->type }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $quarry->tier }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $quarry->min_player_level }}</td>
                        <td class="px-6 py-3 flex gap-2 justify-end">
                            <a href="{{ route('dashboard.wildhunt.quarries.edit', $quarry->id) }}"
                               class="bg-yellow-400 hover:bg-yellow-500 px-3 py-1 rounded text-xs font-semibold text-gray-900">Edit</a>
                            <a href="{{ route('dashboard.wildhunt.monsters.create', ['quarry_id' => $quarry->id]) }}"
                               class="bg-purple-500 hover:bg-purple-600 px-3 py-1 rounded text-xs font-semibold text-white">+ Monster</a>
                            <form method="POST" action="{{ route('dashboard.wildhunt.quarries.destroy', $quarry->id) }}"
                                  class="inline" onsubmit="return confirm('Delete this quarry?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-xs font-semibold text-white">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

@endsection