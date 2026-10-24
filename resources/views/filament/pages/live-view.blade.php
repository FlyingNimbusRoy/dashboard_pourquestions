<x-filament-panels::page>
    <div wire:poll.30000ms>
        @php $snap = \App\Models\DashLiveSnapshot::orderByDesc('id')->first(); @endphp

        @if ($snap)
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-6">
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
                    <p class="text-sm text-gray-500">Open rooms</p>
                    <p class="text-3xl font-bold text-gray-950">{{ $snap->open_rooms }}</p>
                </div>
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
                    <p class="text-sm text-gray-500">Players online</p>
                    <p class="text-3xl font-bold text-gray-950">{{ $snap->players_online }}</p>
                </div>
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
                    <p class="text-sm text-gray-500">Games today</p>
                    <p class="text-3xl font-bold text-gray-950">{{ $snap->games_today }}</p>
                </div>
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
                    <p class="text-sm text-gray-500">Last snapshot</p>
                    <p class="text-sm font-medium text-gray-700 mt-1">{{ $snap->snapshot_at?->diffForHumans() ?? '—' }}</p>
                </div>
            </div>

            @if (!empty($snap->rooms_per_phase))
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 mb-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Rooms per phase</h3>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($snap->rooms_per_phase as $phase => $count)
                            <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium bg-violet-100 text-violet-800">
                                {{ ucfirst($phase) }}: {{ $count }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($snap->failed_post_count > 0 || $snap->retry_queue_size > 0)
                <div class="rounded-xl bg-amber-50 p-4 ring-1 ring-amber-200">
                    <h3 class="text-sm font-semibold text-amber-800 mb-1">Analytics health</h3>
                    <p class="text-sm text-amber-700">
                        Failed posts: {{ $snap->failed_post_count }} &middot; Retry queue: {{ $snap->retry_queue_size }}
                    </p>
                </div>
            @endif
        @else
            <div class="rounded-xl bg-white p-6 text-center shadow-sm ring-1 ring-gray-950/5">
                <p class="text-gray-500">No snapshot yet — socket server may not be running.</p>
                <p class="text-xs text-gray-400 mt-1">The socket server posts a snapshot every 60 seconds.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
