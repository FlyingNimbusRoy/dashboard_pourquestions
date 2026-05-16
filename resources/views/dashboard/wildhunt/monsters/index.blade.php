@extends('dashboard')
@section('content')

    <h1 class="text-2xl font-bold mb-4">Monsters</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('dashboard.wildhunt.monsters.create') }}"
           class="px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700">
            + New Monster
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex gap-3 mb-6 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name…"
               class="border border-gray-300 rounded py-2 px-3 text-sm">
        <select name="quarry_id" class="border border-gray-300 rounded py-2 px-3 text-sm">
            <option value="">All quarries</option>
            @foreach($quarries as $q)
                <option value="{{ $q->id }}" {{ request('quarry_id') == $q->id ? 'selected' : '' }}>
                    [T{{ $q->tier }}] {{ $q->title }}
                </option>
            @endforeach
        </select>
        <select name="is_boss" class="border border-gray-300 rounded py-2 px-3 text-sm">
            <option value="">Boss + Pool</option>
            <option value="1" {{ request('is_boss') === '1' ? 'selected' : '' }}>Boss only</option>
            <option value="0" {{ request('is_boss') === '0' ? 'selected' : '' }}>Pool only</option>
        </select>
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded text-sm">Filter</button>
        <a href="{{ route('dashboard.wildhunt.monsters.index') }}"
           class="bg-gray-100 hover:bg-gray-200 text-gray-500 font-bold py-2 px-4 rounded text-sm">Clear</a>
    </form>

    <div class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Icon</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quarry</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lv</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">HP</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Def</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exp</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Boss</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            @forelse($monsters as $m)
                <tr class="hover:bg-gray-50 {{ $m->is_boss ? 'bg-yellow-50' : '' }}">
                    <td class="px-6 py-3">
                        @if(str_ends_with($m->icon, '.png'))
                            <img src="{{ asset('wildhunt-icons/' . $m->icon) }}" class="w-8 h-8 object-contain">
                        @else
                            <span class="text-xl">{{ $m->icon }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $m->name }}</td>
                    <td class="px-6 py-3 text-gray-500 text-sm">
                        @if($m->quarry_title)
                            <span class="text-gray-400">[T{{ $m->quarry_tier }}]</span> {{ $m->quarry_title }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-3 text-gray-500">{{ $m->level }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $m->hp }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $m->defense }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $m->exp_reward }}</td>
                    <td class="px-6 py-3">
                        @if($m->is_boss)
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-semibold">Boss</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 flex gap-2 justify-end">
                        <a href="{{ route('dashboard.wildhunt.monsters.edit', $m->id) }}"
                           class="bg-yellow-400 hover:bg-yellow-500 px-3 py-1 rounded text-xs font-semibold text-gray-900">Edit</a>
                        <form method="POST" action="{{ route('dashboard.wildhunt.monsters.destroy', $m->id) }}"
                              class="inline" onsubmit="return confirm('Delete {{ $m->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-xs font-semibold text-white">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="py-4 px-6 text-center text-gray-500">No monsters found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $monsters->links() }}</div>

@endsection