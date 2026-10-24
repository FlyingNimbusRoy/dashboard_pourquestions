<x-filament-panels::page>
    @php
        $data = $this->getViewData();
        $total = $data['total'];
        $statusBreakdown = $data['statusBreakdown'];
        $completionRate = $data['completionRate'];
        $abandonRate = $data['abandonRate'];
        $versionBreakdown = $data['versionBreakdown'];
        $abandonedByPhase = $data['abandonedByPhase'];
        $trend = $data['trend'];
        $highTimeout = $data['highTimeout'];
        $since = $data['since'];
    @endphp

    @if ($total === 0)
        <div class="rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-950/5">
            <p class="text-gray-500">No game sessions ingested yet.</p>
            <p class="text-xs text-gray-400 mt-1">Data appears here once the socket server starts posting summaries to <code>/internal/game-session</code>.</p>
        </div>
    @else

        {{-- Headline stats --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-6">
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
                <p class="text-sm text-gray-500">Games (30 d)</p>
                <p class="text-3xl font-bold text-gray-950">{{ number_format($total) }}</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
                <p class="text-sm text-gray-500">Completion rate</p>
                <p class="text-3xl font-bold {{ $completionRate >= 70 ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $completionRate !== null ? $completionRate.'%' : '—' }}
                </p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
                <p class="text-sm text-gray-500">Abandonment rate</p>
                <p class="text-3xl font-bold {{ $abandonRate <= 10 ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $abandonRate !== null ? $abandonRate.'%' : '—' }}
                </p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
                <p class="text-sm text-gray-500">Ended early</p>
                <p class="text-3xl font-bold text-gray-950">{{ number_format($statusBreakdown->get('ended_early', 0)) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">

            {{-- App version breakdown --}}
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">App version breakdown (last 30 d)</h3>
                @if ($versionBreakdown->isEmpty())
                    <p class="text-sm text-gray-400">No data.</p>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 border-b border-gray-100">
                                <th class="pb-2 font-medium">Version</th>
                                <th class="pb-2 font-medium text-right">Games</th>
                                <th class="pb-2 font-medium text-right">Avg duration</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($versionBreakdown as $row)
                                <tr>
                                    <td class="py-1.5 font-mono text-xs">{{ $row->app_version }}</td>
                                    <td class="py-1.5 text-right">{{ number_format($row->cnt) }}</td>
                                    <td class="py-1.5 text-right text-gray-500">
                                        {{ $row->avg_duration_s ? gmdate('i:s', (int) $row->avg_duration_s) : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- Where games died (abandoned by phase) --}}
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Abandoned games — last phase</h3>
                @if ($abandonedByPhase->isEmpty())
                    <p class="text-sm text-gray-400">No abandoned games in the last 30 days.</p>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 border-b border-gray-100">
                                <th class="pb-2 font-medium">Last phase</th>
                                <th class="pb-2 font-medium text-right">Count</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($abandonedByPhase as $row)
                                <tr>
                                    <td class="py-1.5">{{ $row->last_phase_type ?? 'Unknown' }}</td>
                                    <td class="py-1.5 text-right">{{ $row->cnt }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- High-timeout questions --}}
        @if ($highTimeout->isNotEmpty())
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-1">High-timeout questions <span class="font-normal text-gray-400">(min 10 attempts)</span></h3>
                <p class="text-xs text-gray-400 mb-3">Questions where players frequently run out of time — may indicate text too long or question unclear.</p>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 border-b border-gray-100">
                            <th class="pb-2 font-medium">Question ID</th>
                            <th class="pb-2 font-medium text-right">Timeouts</th>
                            <th class="pb-2 font-medium text-right">Attempts</th>
                            <th class="pb-2 font-medium text-right">Timeout %</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($highTimeout as $row)
                            <tr>
                                <td class="py-1.5 font-mono text-xs">#{{ $row->question_id }}</td>
                                <td class="py-1.5 text-right">{{ $row->timeouts }}</td>
                                <td class="py-1.5 text-right text-gray-500">{{ $row->attempts }}</td>
                                <td class="py-1.5 text-right font-medium {{ $row->timeout_pct >= 40 ? 'text-red-600' : 'text-amber-600' }}">
                                    {{ $row->timeout_pct }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- 14-day trend --}}
        @if ($trend->isNotEmpty())
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">14-day trend</h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 border-b border-gray-100">
                            <th class="pb-2 font-medium">Date</th>
                            <th class="pb-2 font-medium text-right">Games</th>
                            <th class="pb-2 font-medium text-right">Abandoned</th>
                            <th class="pb-2 font-medium text-right">Completion</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($trend as $row)
                            <tr>
                                <td class="py-1.5 text-gray-500">{{ \Carbon\Carbon::parse($row->date)->format('M j') }}</td>
                                <td class="py-1.5 text-right">{{ $row->games_total }}</td>
                                <td class="py-1.5 text-right {{ $row->games_abandoned > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $row->games_abandoned }}</td>
                                <td class="py-1.5 text-right {{ $row->completion_rate >= 0.7 ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $row->completion_rate !== null ? round($row->completion_rate * 100).'%' : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    @endif
</x-filament-panels::page>
