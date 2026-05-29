@extends('dashboard')
@section('content')

    <h1 class="text-2xl font-bold mb-4">Pet Definitions</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('dashboard.wildhunt.pets.create') }}"
           class="px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700">
            + New Pet
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex gap-3 mb-6 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name…"
               class="border border-gray-300 rounded py-2 px-3 text-sm">
        <select name="rarity" class="border border-gray-300 rounded py-2 px-3 text-sm">
            <option value="">All rarities</option>
            @foreach(['bonded','common','rare','legendary'] as $r)
                <option value="{{ $r }}" {{ request('rarity') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded text-sm">Filter</button>
        <a href="{{ route('dashboard.wildhunt.pets.index') }}"
           class="bg-gray-100 hover:bg-gray-200 text-gray-500 font-bold py-2 px-4 rounded text-sm">Clear</a>
    </form>

    <div class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Icon</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rarity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">HP×</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ATK×</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Taunt</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Personality</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            @forelse($pets as $p)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 text-xl">{{ $p->icon }}</td>
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $p->name }}</td>
                    <td class="px-6 py-3 text-gray-500 font-mono text-sm">{{ $p->slug }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 rounded text-xs font-semibold
                            {{ $p->rarity === 'legendary' ? 'bg-yellow-100 text-yellow-800' :
                               ($p->rarity === 'rare'      ? 'bg-purple-100 text-purple-800' :
                               ($p->rarity === 'common'    ? 'bg-blue-100 text-blue-800' :
                                                             'bg-gray-100 text-gray-700')) }}">
                            {{ ucfirst($p->rarity) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-gray-500 text-sm">{{ $p->source }}</td>
                    <td class="px-6 py-3 text-gray-500 text-sm">{{ $p->hp_multiplier }}</td>
                    <td class="px-6 py-3 text-gray-500 text-sm">{{ $p->attack_multiplier }}</td>
                    <td class="px-6 py-3 text-sm">
                        @if($p->taunt)
                            <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs font-semibold">Yes</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-gray-500 text-sm">{{ $p->personality }}</td>
                    <td class="px-6 py-3 flex gap-2 justify-end">
                        <a href="{{ route('dashboard.wildhunt.pets.icons', $p->id) }}"
                           class="bg-purple-100 hover:bg-purple-200 px-3 py-1 rounded text-xs font-semibold text-purple-800"
                           title="Manage PNG icons">🖼 Icons</a>
                        <a href="{{ route('dashboard.wildhunt.pets.edit', $p->id) }}"
                           class="bg-yellow-400 hover:bg-yellow-500 px-3 py-1 rounded text-xs font-semibold text-gray-900">Edit</a>
                        <form method="POST" action="{{ route('dashboard.wildhunt.pets.destroy', $p->id) }}"
                              class="inline" onsubmit="return confirm('Delete {{ $p->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-xs font-semibold text-white">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="py-4 px-6 text-center text-gray-500">No pet definitions found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $pets->links() }}</div>

@endsection
