@extends('dashboard')
@section('content')

    <h1 class="text-2xl font-bold mb-4">Quests</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <p class="text-sm text-gray-500">Scripted monthly quests — pre-built wave sequences with specific monsters.</p>
        <a href="{{ route('dashboard.wildhunt.quests.create') }}"
           class="px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700">+ New Quest</a>
    </div>

    <div class="overflow-x-auto rounded-lg shadow border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rec. Level</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waves</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rewards</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            @forelse($quests as $q)
                @php $waves = json_decode($q->waves, true) ?? []; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $q->title }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $q->recommended_level }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ count($waves) }} wave{{ count($waves) !== 1 ? 's' : '' }}</td>
                    <td class="px-6 py-3 text-sm text-gray-600 space-x-1">
                        @if($q->reward_crests) <span class="text-yellow-700">{{ $q->reward_crests }}cr</span> @endif
                        @if($q->reward_xp)     <span class="text-blue-600">{{ $q->reward_xp }}xp</span>     @endif
                        @if($q->reward_gear_id)  <span class="text-gray-500">+gear</span>  @endif
                        @if($q->reward_pet_slug) <span class="text-purple-600">+pet</span> @endif
                    </td>
                    <td class="px-6 py-3 flex gap-2 justify-end">
                        <a href="{{ route('dashboard.wildhunt.quests.edit', $q->id) }}"
                           class="bg-yellow-400 hover:bg-yellow-500 px-3 py-1 rounded text-xs font-semibold text-gray-900">Edit</a>
                        <form method="POST" action="{{ route('dashboard.wildhunt.quests.destroy', $q->id) }}"
                              class="inline" onsubmit="return confirm('Delete {{ $q->title }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-xs font-semibold text-white">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-6 px-6 text-center text-gray-400">No quests yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

@endsection
