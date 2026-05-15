@extends('dashboard')
@section('content')

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Skills</h1>
                <p class="text-sm text-gray-500 mt-1">All class skills — edit values here for rebalancing</p>
            </div>
            <a href="{{ route('dashboard.wildhunt.skills.create') }}"
               class="px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700 transition text-center">
                + New Skill
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" class="flex gap-3 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name…"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <select name="class_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All classes</option>
                <option value="null" {{ request('class_id') === 'null' ? 'selected' : '' }}>Generic</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All types</option>
                @foreach($types as $t)
                    <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
            <select name="damage_type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">All damage types</option>
                @foreach($damageTypes as $dt)
                    <option value="{{ $dt }}" {{ request('damage_type') === $dt ? 'selected' : '' }}>{{ $dt }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition">Filter</button>
            <a href="{{ route('dashboard.wildhunt.skills.index') }}" class="px-4 py-2 text-gray-500 text-sm rounded-lg hover:bg-gray-100 transition">Clear</a>
        </form>

        <div class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Icon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dmg type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lv req</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @forelse($skills as $skill)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3 text-lg">{{ $skill->icon }}</td>
                        <td class="px-6 py-3 font-medium text-gray-800">{{ $skill->name }}</td>
                        <td class="px-6 py-3 text-gray-500 text-xs">{{ $skill->class_name ?? 'Generic' }}</td>
                        <td class="px-6 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ str_starts_with($skill->type, 'atk') ? 'bg-red-50 text-red-700' : '' }}
                            {{ str_starts_with($skill->type, 'def') ? 'bg-blue-50 text-blue-700' : '' }}
                            {{ in_array($skill->type, ['skill','heal']) ? 'bg-green-50 text-green-700' : '' }}">
                            {{ $skill->type }}
                        </span>
                        </td>
                        <td class="px-6 py-3 text-gray-500 text-xs">{{ $skill->damage_type ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $skill->action_cost }}</td>
                        <td class="px-6 py-3 text-gray-500 font-semibold">{{ $skill->value }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $skill->level_required }}</td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="{{ route('dashboard.wildhunt.skills.edit', $skill->id) }}"
                               class="bg-yellow-400 px-2 py-1 rounded text-blue-600 hover:bg-yellow-500">Edit</a>
                            <form method="POST" action="{{ route('dashboard.wildhunt.skills.destroy', $skill->id) }}"
                                  class="inline" onsubmit="return confirm('Delete {{ $skill->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="bg-red-500 px-2 py-1 rounded hover:bg-red-700 text-white">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-6 py-6 text-center text-gray-400">No skills found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $skills->links() }}</div>

    </div>

@endsection