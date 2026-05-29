@extends('dashboard')
@section('content')

    <h1 class="text-2xl font-bold mb-4">Challenges</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('dashboard.wildhunt.challenges.create') }}"
           class="px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700">+ New Challenge</a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex gap-3 mb-6 flex-wrap">
        <select name="type" class="border border-gray-300 rounded py-2 px-3 text-sm">
            <option value="">All types</option>
            @foreach(['daily','weekly','monthly'] as $t)
                <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        <select name="period" class="border border-gray-300 rounded py-2 px-3 text-sm">
            <option value="">All periods</option>
            <option value="active"   {{ request('period') === 'active'   ? 'selected' : '' }}>Active now</option>
            <option value="upcoming" {{ request('period') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
            <option value="expired"  {{ request('period') === 'expired'  ? 'selected' : '' }}>Expired</option>
        </select>
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded text-sm">Filter</button>
        <a href="{{ route('dashboard.wildhunt.challenges.index') }}"
           class="bg-gray-100 hover:bg-gray-200 text-gray-500 font-bold py-2 px-4 rounded text-sm">Clear</a>
    </form>

    <div class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Objective</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reward</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active from</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Until</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            @forelse($challenges as $c)
                @php
                    $now    = now();
                    $from   = \Carbon\Carbon::parse($c->active_from);
                    $until  = \Carbon\Carbon::parse($c->active_until);
                    $status = $now->between($from, $until) ? 'active'
                            : ($now->lt($from) ? 'upcoming' : 'expired');
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded text-xs font-semibold
                            {{ $c->type === 'daily'   ? 'bg-green-100 text-green-700' :
                               ($c->type === 'weekly'  ? 'bg-blue-100 text-blue-700'  :
                                                         'bg-purple-100 text-purple-700') }}">
                            {{ ucfirst($c->type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-900">
                        {{ $c->icon ? $c->icon . ' ' : '' }}{{ $c->title }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $objectiveTypes[$c->objective_type] ?? $c->objective_type }}
                        <span class="text-gray-400">(×{{ $c->objective_target }})</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 space-x-1">
                        @if($c->reward_crests) <span class="text-yellow-700">{{ $c->reward_crests }}cr</span> @endif
                        @if($c->reward_xp)     <span class="text-blue-600">{{ $c->reward_xp }}xp</span>     @endif
                        @if($c->reward_gear_id)  <span class="text-gray-500">+gear</span>  @endif
                        @if($c->reward_pet_slug) <span class="text-purple-600">+pet</span> @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $from->format('d M H:i') }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $until->format('d M H:i') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded text-xs font-semibold
                            {{ $status === 'active'   ? 'bg-green-100 text-green-700'  :
                               ($status === 'upcoming' ? 'bg-yellow-100 text-yellow-700' :
                                                         'bg-gray-100 text-gray-500') }}">
                            {{ ucfirst($status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 flex gap-2 justify-end">
                        <form method="POST" action="{{ route('dashboard.wildhunt.challenges.duplicate', $c->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded text-xs font-semibold text-gray-700"
                                    title="Duplicate">⧉</button>
                        </form>
                        <a href="{{ route('dashboard.wildhunt.challenges.edit', $c->id) }}"
                           class="bg-yellow-400 hover:bg-yellow-500 px-3 py-1 rounded text-xs font-semibold text-gray-900">Edit</a>
                        <form method="POST" action="{{ route('dashboard.wildhunt.challenges.destroy', $c->id) }}"
                              class="inline" onsubmit="return confirm('Delete this challenge?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-xs font-semibold text-white">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="py-6 px-4 text-center text-gray-400">No challenges yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $challenges->links() }}</div>

@endsection
