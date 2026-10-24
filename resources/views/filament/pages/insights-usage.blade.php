<x-filament-panels::page>
    @php
        $data = $this->getViewData();
        extract($data);
    @endphp

    {{-- Headline stats --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-6">
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
            <p class="text-sm text-gray-500">Total questions</p>
            <p class="text-3xl font-bold text-gray-950">{{ number_format($totalQuestions) }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
            <p class="text-sm text-gray-500">Ever asked</p>
            <p class="text-3xl font-bold text-emerald-600">{{ number_format($distinctAsked) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $pctAsked }}% of bank</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
            <p class="text-sm text-gray-500">Never asked</p>
            <p class="text-3xl font-bold text-amber-600">{{ number_format($neverAskedCount) }}</p>
            <a href="{{ $neverAskedUrl }}" class="text-xs text-purple-600 hover:underline mt-1 block">Open list →</a>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
            <p class="text-sm text-gray-500">Orphan IDs</p>
            <p class="text-3xl font-bold {{ $orphanCount > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ number_format($orphanCount) }}</p>
            <p class="text-xs text-gray-400 mt-1">asked &amp; later deleted</p>
        </div>
    </div>

    {{-- U1: Top 25 most asked --}}
    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-1">Top 25 most asked questions</h3>
        <p class="text-xs text-gray-400 mb-3">Questions that appear most often across all recorded games. Share is % of total asks ({{ number_format($totalAsks) }}).</p>
        @if (empty($top25))
            <p class="text-sm text-gray-400">No game data yet — share counts appear once games with question lists are recorded.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 border-b border-gray-100">
                            <th class="pb-2 font-medium w-6">#</th>
                            <th class="pb-2 font-medium">Question</th>
                            <th class="pb-2 font-medium">Category</th>
                            <th class="pb-2 font-medium text-center">Diff</th>
                            <th class="pb-2 font-medium text-right">Asked</th>
                            <th class="pb-2 font-medium text-right">Share</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($top25 as $i => $row)
                            <tr>
                                <td class="py-1.5 text-gray-400 text-xs">{{ $i + 1 }}</td>
                                <td class="py-1.5">
                                    <a href="{{ \App\Filament\Resources\Questions\QuestionResource::getUrl('edit', ['record' => $row['id']]) }}"
                                       class="text-purple-700 hover:underline" target="_blank">
                                        {{ \Illuminate\Support\Str::limit($row['vraag'], 80) }}
                                    </a>
                                </td>
                                <td class="py-1.5 text-gray-500 text-xs">{{ $row['category'] }}</td>
                                <td class="py-1.5 text-center text-gray-500">{{ $row['difficulty'] }}</td>
                                <td class="py-1.5 text-right font-medium">{{ number_format($row['asks']) }}</td>
                                <td class="py-1.5 text-right text-gray-400">{{ $row['share'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">

        {{-- U3: Histogram --}}
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Asks distribution</h3>
            <p class="text-xs text-gray-400 mb-4">How many times was each question asked? Bars are scaled to the tallest bucket.</p>
            @foreach ($histogram as $label => $count)
                @php $pct = round($count / $histogramMax * 100); @endphp
                <div class="mb-3">
                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                        <span>{{ $label === '0' ? 'Never' : $label }} ask{{ $label === '1' ? '' : 's' }}</span>
                        <span class="text-gray-400">{{ number_format($count) }}</span>
                    </div>
                    <div class="h-4 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full {{ $label === '0' ? 'bg-amber-400' : 'bg-purple-500' }}"
                             style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- U2: Never / asked-once panel --}}
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Low-usage questions</h3>
            <p class="text-xs text-gray-400 mb-4">Questions that have never appeared in a game, or only once. Use the Questions list to bulk-assign gamepack, category, or add to a review batch.</p>
            <div class="flex gap-6 mb-5">
                <div>
                    <p class="text-3xl font-bold text-amber-600">{{ number_format($neverAskedCount) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Never asked</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-700">{{ number_format($askedOnceCount) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Asked once</p>
                </div>
            </div>
            <a href="{{ $neverAskedUrl }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700 transition-colors">
                Open Never asked tab →
            </a>
        </div>

    </div>

    {{-- U4: Usage breakdown by category / gamepack --}}
    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5"
         x-data="{ tab: 'category' }">
        <div class="flex items-center justify-between mb-1">
            <h3 class="text-sm font-semibold text-gray-700">Usage breakdown</h3>
            <div class="flex gap-1">
                <button @click="tab = 'category'"
                        :class="tab === 'category' ? 'bg-purple-100 text-purple-700' : 'text-gray-500 hover:text-gray-700'"
                        class="rounded-md px-3 py-1 text-xs font-medium transition-colors">
                    By category
                </button>
                <button @click="tab = 'gamepack'"
                        :class="tab === 'gamepack' ? 'bg-purple-100 text-purple-700' : 'text-gray-500 hover:text-gray-700'"
                        class="rounded-md px-3 py-1 text-xs font-medium transition-colors">
                    By gamepack
                </button>
            </div>
        </div>
        <p class="text-xs text-gray-400 mb-4">Total asks per group. Bar width is relative to the group with the most asks.</p>

        <div x-show="tab === 'category'">
            @forelse ($perCategory as $row)
                @php $pct = round($row['asks'] / $maxCategoryAsks * 100); @endphp
                <div class="mb-3">
                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                        <span class="truncate max-w-[55%]">{{ $row['name'] }}</span>
                        <span class="text-gray-400 flex-shrink-0 ml-2">
                            {{ number_format($row['asks']) }} asks
                            &middot; {{ $row['pct'] }}% asked
                            &middot; {{ $row['questions'] }}q
                        </span>
                    </div>
                    <div class="h-3 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full bg-purple-400" style="width: {{ max($pct, $row['asks'] > 0 ? 1 : 0) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">No categories found.</p>
            @endforelse
        </div>

        <div x-show="tab === 'gamepack'" style="display: none;">
            @forelse ($perGamepack as $row)
                @php $pct = round($row['asks'] / $maxGamepackAsks * 100); @endphp
                <div class="mb-3">
                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                        <span class="truncate max-w-[55%]">{{ $row['name'] }}</span>
                        <span class="text-gray-400 flex-shrink-0 ml-2">
                            {{ number_format($row['asks']) }} asks
                            &middot; {{ $row['pct'] }}% asked
                            &middot; {{ $row['questions'] }}q
                        </span>
                    </div>
                    <div class="h-3 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full bg-purple-400" style="width: {{ max($pct, $row['asks'] > 0 ? 1 : 0) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">No gamepacks found.</p>
            @endforelse
        </div>
    </div>

</x-filament-panels::page>
