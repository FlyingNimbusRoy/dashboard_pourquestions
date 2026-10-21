<x-filament-panels::page>
    @php
        $max = max(1, ...array_values($histogram));
        $table = fn (string $title, array $rows) => null;
    @endphp

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([['Questions', $total], ['Asked at least once', $asked.' ('.round($asked / max(1, $total) * 100).'%)'], ['Never asked', $never], ['Total asks', $asks]] as [$label, $value])
            <x-filament::section>
                <div class="text-sm" style="color:#57466e">{{ $label }}</div>
                <div class="text-2xl font-semibold">{{ is_numeric($value) ? number_format($value) : $value }}</div>
            </x-filament::section>
        @endforeach
    </div>
    @if ($orphans > 0)
        <p class="text-sm text-warning-600">{{ $orphans }} question ids in games no longer exist in the question bank.</p>
    @else
        <p class="text-sm" style="color:#57466e">All question ids in games still exist in the question bank (0 deleted).</p>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <x-filament::section heading="Top {{ count($top) }} most asked">
            <table class="w-full text-sm">
                <thead><tr class="text-left" style="color:#57466e"><th>#</th><th>Question</th><th>Category</th><th class="text-right">Asked</th></tr></thead>
                <tbody>
                @foreach ($top as $row)
                    <tr class="border-t" style="border-color:rgba(124,58,237,0.10)">
                        <td class="py-1 pr-2">{{ $row['id'] }}</td>
                        <td class="pr-2"><a class="text-primary-600 underline" href="{{ \App\Filament\Resources\Questions\QuestionResource::getUrl('edit', ['record' => $row['id']]) }}">{{ \Illuminate\Support\Str::limit($row['q']?->vraag ?? '?', 60) }}</a></td>
                        <td class="pr-2">{{ $row['q']?->category?->name ?? '-' }}</td>
                        <td class="text-right font-medium">{{ $row['asks'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </x-filament::section>

        <x-filament::section heading="Times asked per question" description="Number of questions by how often they were asked (sums to {{ number_format($total) }})">
            @foreach ($histogram as $bucket => $n)
                <div class="mb-2 flex items-center gap-3 text-sm">
                    <div class="w-12 shrink-0" style="color:#57466e">{{ $bucket }}x</div>
                    <div class="h-4 flex-1 rounded-full" style="background:rgba(124,58,237,0.08)">
                        <div class="h-4 rounded-full" style="width: {{ $n / $max * 100 }}%; background:#a855f7"></div>
                    </div>
                    <div class="w-14 text-right font-medium">{{ number_format($n) }}</div>
                </div>
            @endforeach
        </x-filament::section>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        @foreach ([['Per category', $categories], ['Per gamepack', $gamepacks]] as [$title, $rows])
            <x-filament::section :heading="$title">
                <table class="w-full text-sm">
                    <thead><tr class="text-left" style="color:#57466e"><th>Name</th><th class="text-right">Questions</th><th class="text-right">Asked</th><th class="text-right">% asked</th><th class="text-right">Total asks</th></tr></thead>
                    <tbody>
                    @foreach ($rows as $r)
                        <tr class="border-t" style="border-color:rgba(124,58,237,0.10)">
                            <td class="py-1">{{ $r['name'] }}</td>
                            <td class="text-right">{{ $r['questions'] }}</td>
                            <td class="text-right">{{ $r['asked'] }}</td>
                            <td class="text-right font-medium">{{ $r['pct'] }}%</td>
                            <td class="text-right">{{ $r['asks'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </x-filament::section>
        @endforeach
    </div>

    <div class="flex items-center justify-between">
        <span class="text-sm" style="color:#57466e">Work with the {{ $never }} never-asked questions in bulk (set category, gamepack, difficulty):</span>
        <x-filament::button tag="a" :href="$neverUrl" size="sm">Open in Questions</x-filament::button>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
