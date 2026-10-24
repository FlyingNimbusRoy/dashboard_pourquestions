<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Header row: title + pace badge --}}
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide" style="color:#57466e">{{ $data['month_label'] }}</p>
                <div class="flex items-end gap-2 mt-0.5">
                    <span class="text-4xl font-bold leading-none">{{ $data['total'] }}</span>
                    <span class="text-base mb-0.5" style="color:#57466e">/ {{ $data['target'] }} questions</span>
                </div>
            </div>
            <span class="mt-1 rounded-full px-2.5 py-1 text-xs font-semibold text-white"
                  style="background: {{ $paceColor }}">{{ $paceLabel }}</span>
        </div>

        {{-- Progress bar with expected-by-today marker --}}
        <div class="relative mb-1">
            <div class="h-2.5 w-full overflow-hidden rounded-full" style="background:rgba(124,58,237,0.10)">
                <div class="h-2.5 rounded-full transition-all duration-500"
                     style="width:{{ $data['pct_complete'] }}%; background:#a855f7"></div>
            </div>
            {{-- Expected position needle --}}
            @if ($data['pct_expected'] > 0 && $data['pct_expected'] < 100)
                <div class="absolute top-0 h-2.5 w-0.5 rounded-full"
                     style="left:{{ $data['pct_expected'] }}%; background:rgba(124,58,237,0.45);"
                     title="Expected by today: {{ $data['expected'] }}"></div>
            @endif
        </div>
        <div class="mb-4 flex justify-between text-xs" style="color:#57466e">
            <span>Day {{ $data['day_of_month'] }} of {{ $data['days_total'] }}</span>
            <span>
                {{ $data['pct_complete'] }}% complete
                @if ($data['pct_expected'] > 0 && $data['pct_complete'] < 100)
                    · expected {{ $data['expected'] }} by today
                @endif
            </span>
        </div>

        {{-- Contributor breakdown --}}
        @if ($data['contributors']->isNotEmpty())
            <table class="w-full text-sm mb-3">
                <thead>
                    <tr style="color:#57466e">
                        <th class="pb-1.5 text-left font-medium">Contributor</th>
                        <th class="pb-1.5 text-right font-medium">This month</th>
                        <th class="pb-1.5 text-right font-medium">All time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['contributors'] as $contributor)
                        <tr class="border-t" style="border-color:rgba(124,58,237,0.10)">
                            <td class="py-1.5">
                                <span>{{ $contributor->name }}</span>
                                @if ($contributor->current_streak >= 3)
                                    <span class="ml-1.5 inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-xs font-semibold"
                                          style="background:rgba(251,191,36,0.15);color:#d97706"
                                          title="🔥 {{ $contributor->current_streak }}-day streak this month">
                                        🔥 {{ $contributor->current_streak }}d
                                    </span>
                                @elseif ($contributor->best_streak >= 3)
                                    <span class="ml-1.5 inline-flex items-center rounded-full px-1.5 py-0.5 text-xs"
                                          style="background:rgba(124,58,237,0.08);color:#57466e"
                                          title="Best streak this month: {{ $contributor->best_streak }} days">
                                        best {{ $contributor->best_streak }}d
                                    </span>
                                @endif
                            </td>
                            <td class="py-1.5 text-right font-semibold">{{ $contributor->month_count }}</td>
                            <td class="py-1.5 text-right" style="color:#57466e">{{ number_format($contributor->total_count) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="mb-3 text-sm" style="color:#57466e">No questions added yet this month.</p>
        @endif

        {{-- Footer: what's still needed --}}
        @if ($data['remaining'] > 0 && $data['days_left'] > 0)
            <p class="text-xs" style="color:#57466e">
                {{ $data['remaining'] }} more to go
                &middot;
                {{ $data['needed_per_day'] }} per day over the remaining {{ $data['days_left'] }} day{{ $data['days_left'] === 1 ? '' : 's' }}
            </p>
        @elseif ($data['total'] >= $data['target'])
            <p class="text-xs font-medium" style="color:#10b981">Monthly target achieved — great work!</p>
        @elseif ($data['days_left'] === 0)
            <p class="text-xs" style="color:#57466e">Month ended · {{ $data['remaining'] }} short of target.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
