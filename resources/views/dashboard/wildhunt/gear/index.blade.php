@extends('dashboard')
@section('content')

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Gear</h1>
                <p class="text-sm text-gray-500 mt-1">Store items and gauntlet loot</p>
            </div>
            <a href="{{ route('dashboard.wildhunt.gear.create') }}"
               class="px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700 transition text-center">
                + New Item
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" class="flex gap-3 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name…"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <select name="slot" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All slots</option>
                @foreach($slots as $s)
                    <option value="{{ $s }}" {{ request('slot') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="is_gauntlet_loot" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Store + Gauntlet</option>
                <option value="1" {{ request('is_gauntlet_loot') === '1' ? 'selected' : '' }}>Gauntlet loot only</option>
                <option value="0" {{ request('is_gauntlet_loot') === '0' ? 'selected' : '' }}>Store only</option>
            </select>
            <select name="class_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All classes</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition">Filter</button>
            <a href="{{ route('dashboard.wildhunt.gear.index') }}" class="px-4 py-2 text-gray-500 text-sm rounded-lg hover:bg-gray-100 transition">Clear</a>
        </form>

        <div class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slot</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bonuses</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @forelse($gear as $item)
                    <tr class="border-b hover:bg-gray-50 {{ $item->is_gauntlet_loot ? 'bg-purple-50' : '' }}">
                        <td class="px-6 py-3 font-medium text-gray-800">{{ $item->name }}</td>
                        <td class="px-6 py-3 text-gray-500 text-xs">{{ $item->slot }}</td>
                        <td class="px-6 py-3 text-gray-500 text-xs">{{ $item->class_name ?? 'All' }}</td>
                        <td class="px-6 py-3 text-xs">
                            @if($item->is_gauntlet_loot)
                                <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full font-semibold">
                                Gauntlet T{{ $item->gauntlet_tier }}{{ $item->quarry_title ? ' · ' . $item->quarry_title : '' }}
                            </span>
                            @else
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">Store</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-xs text-gray-500">
                            @if($item->hp_bonus)       HP +{{ $item->hp_bonus }} @endif
                            @if($item->attack_bonus)   ATK +{{ $item->attack_bonus }} @endif
                            @if($item->defense_bonus)  DEF +{{ $item->defense_bonus }} @endif
                            @if($item->arcana_bonus)   ARC +{{ $item->arcana_bonus }} @endif
                            @if($item->regen)          REGEN {{ $item->regen }} @endif
                            @if($item->resistances)    RES:{{ $item->resistances }} @endif
                        </td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('dashboard.wildhunt.gear.edit', $item->id) }}"
                               class="bg-yellow-400 px-2 py-1 rounded text-blue-600 hover:bg-yellow-500">Edit</a>
                            <form method="POST" action="{{ route('dashboard.wildhunt.gear.destroy', $item->id) }}"
                                  class="inline" onsubmit="return confirm('Delete {{ $item->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="bg-red-500 px-2 py-1 rounded hover:bg-red-700 text-white">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-6 text-center text-gray-400">No gear found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $gear->links() }}</div>

    </div>

@endsection