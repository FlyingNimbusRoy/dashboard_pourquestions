<x-filament-widgets::widget>
    <x-filament::section heading="Recent activity" :collapsible="false">

        @if ($items->isEmpty())
            <p class="text-sm text-gray-400 py-2">No activity yet.</p>
        @else
            <div class="divide-y divide-gray-100">
                @foreach ($items as $item)
                    @php
                        $badgeColor = match ($item['type']) {
                            'question'     => 'bg-violet-100 text-violet-700',
                            'issue'        => 'bg-red-100 text-red-700',
                            'note'         => 'bg-gray-100 text-gray-600',
                            'toggle'       => $item['label'] === 'enabled' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700',
                            'announcement' => 'bg-blue-100 text-blue-700',
                            default        => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <div class="flex items-start gap-3 py-2.5">
                        <span class="mt-0.5 shrink-0 rounded px-1.5 py-0.5 text-xs font-medium {{ $badgeColor }}">
                            {{ $item['label'] }}
                        </span>
                        <div class="min-w-0 flex-1">
                            @if ($item['url'])
                                <a href="{{ $item['url'] }}" class="text-sm text-gray-800 hover:text-purple-600 hover:underline">{{ $item['text'] }}</a>
                            @else
                                <span class="text-sm text-gray-800">{{ $item['text'] }}</span>
                            @endif
                            <span class="ml-1 text-xs text-gray-400">
                                &middot; {{ $item['who'] ?? 'system' }}
                            </span>
                        </div>
                        <span class="shrink-0 text-xs text-gray-400">{{ $item['at']?->diffForHumans() ?? '—' }}</span>
                    </div>
                @endforeach
            </div>
        @endif

    </x-filament::section>
</x-filament-widgets::widget>
