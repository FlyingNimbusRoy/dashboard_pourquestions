<x-filament-panels::page>
    @php $data = $this->getViewData(); @endphp

    {{-- Search bar --}}
    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
        <input wire:model.live.debounce.400ms="query"
               type="text"
               placeholder="Game ID (e.g. 42) or PIN (e.g. POUR1)"
               class="w-full max-w-sm rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500">
        <p class="text-xs text-gray-400 mt-1">Numbers are treated as game IDs. Letters/mixed are treated as PINs (PINs repeat, so you may see multiple games).</p>
    </div>

    @if ($data['mode'] === 'empty')
        {{-- Idle state --}}

    @elseif ($data['mode'] === 'pin')
        {{-- PIN search results --}}
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Games with PIN "{{ $data['pin'] }}"</h3>
            @forelse ($data['sessions'] as $session)
                <div class="py-3 border-b last:border-0 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium">
                            Game #{{ $session->game_id }}
                            <span class="text-gray-400 font-normal">
                                &middot; {{ $session->app_version ?? '—' }}
                                &middot; {{ $session->player_count }} players
                                &middot; {{ $session->status }}
                            </span>
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $session->started_at?->format('Y-m-d H:i') ?? '—' }}
                            @if ($session->duration_s)
                                &middot; {{ gmdate('G\hi\ms\s', $session->duration_s) }}
                            @endif
                        </p>
                    </div>
                    <button wire:click="$set('query', '{{ $session->game_id }}')"
                            class="text-xs text-purple-600 hover:underline flex-shrink-0">
                        View details →
                    </button>
                </div>
            @empty
                <p class="text-sm text-gray-400">No games found with PIN "{{ $data['pin'] }}".</p>
            @endforelse
        </div>

    @elseif ($data['mode'] === 'game_id')
        @php
            $game    = $data['game'];
            $session = $data['session'];
            $support = $data['support'];
            $notes   = $data['notes'];
        @endphp

        @if ($game === null && $session === null)
            <div class="rounded-xl bg-white p-6 text-center shadow-sm ring-1 ring-gray-950/5">
                <p class="text-gray-500">No game found with ID {{ $this->query }}.</p>
            </div>
        @else
            {{-- Game summary --}}
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 mb-4">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700">Game #{{ $game?->id ?? $this->query }}</h3>
                    @if ($game?->host)
                        <a href="{{ \App\Filament\Resources\Players\PlayerResource::getUrl('view', ['record' => $game->host_id]) }}"
                           class="text-xs text-purple-600 hover:underline">
                            View host account →
                        </a>
                    @endif
                </div>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-xs text-gray-500">Name</dt>
                        <dd class="font-medium">{{ $game?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Created</dt>
                        <dd>{{ $game?->created_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Host</dt>
                        <dd>{{ $game?->host?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Questions drawn</dt>
                        <dd>{{ $game ? count($game->question_array ?? []) : '—' }}</dd>
                    </div>
                    @if ($game)
                        <div>
                            <dt class="text-xs text-gray-500">Finished</dt>
                            <dd>{{ $game->is_finished ? 'Yes' : 'No' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Cancelled</dt>
                            <dd>{{ $game->is_cancelled ? 'Yes' : 'No' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Game length</dt>
                            <dd>{{ $game->game_length ?? '—' }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            @if ($session)
                {{-- Analytics session --}}
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Analytics session</h3>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm sm:grid-cols-4">
                        <div>
                            <dt class="text-xs text-gray-500">Status</dt>
                            <dd>
                                <span class="inline-block rounded px-1.5 py-0.5 text-xs font-medium
                                    {{ match($session->status) {
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'abandoned' => 'bg-amber-100 text-amber-700',
                                        default     => 'bg-gray-100 text-gray-700',
                                    } }}">
                                    {{ $session->status }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">PIN</dt>
                            <dd class="font-mono">{{ $session->pin ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">App version</dt>
                            <dd class="font-mono">{{ $session->app_version ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Players</dt>
                            <dd>{{ $session->player_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Questions asked</dt>
                            <dd>{{ $session->question_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Started</dt>
                            <dd>{{ $session->started_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Ended</dt>
                            <dd>{{ $session->ended_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Duration</dt>
                            <dd>{{ $session->duration_s ? gmdate('G\hi\ms\s', $session->duration_s) : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Last phase</dt>
                            <dd>{{ $session->last_phase_type ?? '—' }}{{ $session->last_phase_round ? ' round '.$session->last_phase_round : '' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Lead changes</dt>
                            <dd>{{ $session->lead_changes ?? '—' }}</dd>
                        </div>
                    </dl>
                    @php $settings = $session->settings ?? []; @endphp
                    @if (!empty($settings))
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-500 mb-1">Game settings</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($settings as $key => $val)
                                    <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                                        {{ $key }}: {{ is_bool($val) ? ($val ? 'yes' : 'no') : $val }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="rounded-xl bg-amber-50 p-3 text-sm text-amber-700 ring-1 ring-amber-200 mb-4">
                    No analytics session found for this game. Either it was played before analytics were enabled, or the session summary was never posted.
                </div>
            @endif

            {{-- Support messages --}}
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 mb-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">
                    Support messages
                    @if ($support->isNotEmpty())
                        <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700">{{ $support->count() }}</span>
                    @endif
                </h3>
                @forelse ($support as $msg)
                    <div class="py-2 border-b last:border-0 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm">{{ \Illuminate\Support\Str::limit($msg->message, 100) }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $msg->type }} &middot; {{ $msg->status }} &middot; {{ $msg->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <a href="{{ \App\Filament\Resources\WorkQueue\SupportMessageResource::getUrl('view', ['record' => $msg->id]) }}"
                           class="text-xs text-purple-600 hover:underline flex-shrink-0">View →</a>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No support messages linked to this game.</p>
                @endforelse
            </div>

            {{-- Internal notes --}}
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5" x-data="{ showForm: false }">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700">Internal notes</h3>
                    <button @click="showForm = !showForm"
                            class="text-xs text-purple-600 hover:underline">
                        + Add note
                    </button>
                </div>

                <div x-show="showForm" x-cloak class="mb-3"
                     x-data="{ note: '' }">
                    <textarea x-model="note"
                              rows="2"
                              placeholder="Staff note about this game…"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500"></textarea>
                    <button @click="if(note.trim().length >= 3) { $wire.addNote({{ $game?->id ?? $this->query }}, note); note = ''; showForm = false; }"
                            class="mt-1.5 rounded-lg bg-purple-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-purple-700 transition-colors">
                        Save note
                    </button>
                </div>

                @include('filament.internal-notes', ['notes' => $notes])
            </div>
        @endif
    @endif
</x-filament-panels::page>
