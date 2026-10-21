<x-filament-panels::page>
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([['Games', $summary['total']], ['With question list', $summary['with_list']], ['Without question list', $summary['without_list']], ['Avg questions per game', $summary['avg_questions']]] as [$label, $value])
            <x-filament::section>
                <div class="text-sm" style="color:#57466e">{{ $label }}</div>
                <div class="text-2xl font-semibold">{{ $value }}</div>
            </x-filament::section>
        @endforeach
    </div>
    <p class="text-sm" style="color:#57466e">Completion, shots and player counts are not shown: the game does not fill them in the current data.</p>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-filament::section heading="Most used gamepacks" description="Total times a pack's questions were asked in games (questions without a pack are listed too)">
            <table class="w-full text-sm">
                <thead><tr class="text-left" style="color:#57466e"><th>Gamepack</th><th class="text-right">Questions</th><th class="text-right">% asked</th><th class="text-right">Total asks</th></tr></thead>
                <tbody>
                @foreach ($gamepacks as $r)
                    <tr class="border-t" style="border-color:rgba(124,58,237,0.10)">
                        <td class="py-1">{{ $r['name'] }}</td><td class="text-right">{{ $r['questions'] }}</td>
                        <td class="text-right">{{ $r['pct'] }}%</td><td class="text-right font-medium">{{ $r['asks'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </x-filament::section>
        <x-filament::section heading="Game length" description="Games by game_length value (unit to be confirmed)">
            <div class="flex flex-wrap gap-2 text-sm">
                @foreach ($lengths as $l => $n)
                    <span class="rounded-md px-2 py-1" style="background:rgba(124,58,237,0.08)">{{ $l }}: <strong>{{ $n }}</strong></span>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
