<x-filament-panels::page>
    @if ($this->pairId === null)
        {{-- All done state --}}
        <div class="rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-950/5">
            <x-filament::icon icon="heroicon-o-check-circle" class="mx-auto mb-3 h-12 w-12 text-emerald-500" />
            <p class="text-lg font-semibold text-gray-800">All open pairs reviewed</p>
            <p class="mt-1 text-sm text-gray-500">There are no more open duplicate pairs. New ones appear as questions are added and compared.</p>
            <div class="mt-5 flex justify-center gap-3">
                <a href="{{ \App\Filament\Resources\QuestionSimilarities\QuestionSimilarityResource::getUrl('index') }}"
                   class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                    Back to list
                </a>
            </div>
        </div>
    @else
        @php
            $pair = $this->getPair();
            $a    = $pair->question;
            $b    = $pair->similarQuestion;
            $usage = app(\App\Services\Insights\QuestionUsage::class);
            $asksA = $usage->timesAsked($a->id);
            $asksB = $usage->timesAsked($b->id);
            $score = round($pair->score * 100);
            $isLikely = $pair->isLikelyDuplicate();
        @endphp

        {{-- Score banner --}}
        <div class="rounded-xl {{ $isLikely ? 'bg-red-50 ring-1 ring-red-200' : 'bg-amber-50 ring-1 ring-amber-200' }} p-3 mb-4 flex items-center gap-3">
            <span class="rounded-full {{ $isLikely ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }} px-3 py-1 text-sm font-semibold">
                {{ $score }}% match
            </span>
            <span class="text-sm {{ $isLikely ? 'text-red-700' : 'text-amber-700' }}">
                {{ $isLikely ? 'Likely duplicate' : 'Similar — may or may not be a duplicate' }}
            </span>
            @php
                $signals = $pair->signals ?? [];
                $parts = [];
                if (isset($signals['text'])) $parts[] = 'wording '.round($signals['text'] * 100).'%';
                if (!empty($signals['number_mismatch'])) $parts[] = 'different numbers';
                if (!empty($signals['negation_mismatch'])) $parts[] = 'one negated';
            @endphp
            @if ($parts)
                <span class="text-xs text-gray-500 ml-auto">{{ implode(' · ', $parts) }}</span>
            @endif
        </div>

        {{-- Side-by-side questions --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 mb-4">
            @foreach ([['q' => $a, 'asks' => $asksA, 'label' => 'Question A', 'action' => 'deleteQuestion('.$a->id.')'],
                       ['q' => $b, 'asks' => $asksB, 'label' => 'Question B', 'action' => 'deleteQuestion('.$b->id.')']] as $side)
                @php $q = $side['q']; @endphp
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $side['label'] }}</span>
                        <div class="flex gap-1.5">
                            <x-filament::badge color="gray">#{{ $q->id }}</x-filament::badge>
                            <x-filament::badge>{{ $q->category?->name ?? 'No category' }}</x-filament::badge>
                            <x-filament::badge color="warning">diff {{ $q->difficulty }}</x-filament::badge>
                            @if ($side['asks'] > 0)
                                <x-filament::badge color="info">asked {{ $side['asks'] }}×</x-filament::badge>
                            @else
                                <x-filament::badge color="gray">never asked</x-filament::badge>
                            @endif
                        </div>
                    </div>

                    <p class="text-base font-semibold text-gray-900 mb-3 leading-snug">{{ $q->vraag }}</p>

                    <div class="flex flex-wrap gap-1.5 mb-3">
                        @foreach ($q->answers->sortBy('id') as $answer)
                            @if ($answer->is_correct)
                                <x-filament::badge color="success" icon="heroicon-m-check">{{ $answer->answer }}</x-filament::badge>
                            @else
                                <x-filament::badge color="gray">{{ $answer->answer }}</x-filament::badge>
                            @endif
                        @endforeach
                    </div>

                    <div class="flex gap-2 mt-3 pt-3 border-t border-gray-100">
                        <a href="{{ \App\Filament\Resources\Questions\QuestionResource::getUrl('edit', ['record' => $q->id]) }}"
                           target="_blank"
                           class="text-xs text-purple-600 hover:underline flex items-center gap-1">
                            Edit →
                        </a>
                        @if ($side['asks'] > 0)
                            <span class="text-xs text-amber-600 ml-auto">⚠ Asked {{ $side['asks'] }}× — deletion will break historical records</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Actions --}}
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
            <p class="text-xs text-gray-500 mb-3">Choose an action for this pair. The next open pair loads automatically.</p>
            <div class="flex flex-wrap gap-2">
                <button wire:click="dismiss"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                    <x-filament::icon icon="heroicon-o-hand-thumb-up" class="h-4 w-4" />
                    Not a duplicate
                </button>
                <button wire:click="resolve"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                    <x-filament::icon icon="heroicon-o-check" class="h-4 w-4" />
                    Mark resolved (edited/merged)
                </button>

                <div class="flex gap-2 ml-auto">
                    @if ($asksA > 0)
                        <button wire:click="deleteQuestion({{ $a->id }})"
                                wire:confirm="Delete Question A (#{{ $a->id }})? It was asked {{ $asksA }}× in past games. This cannot be undone."
                                class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100 transition-colors">
                            <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                            Delete A (asked {{ $asksA }}×)
                        </button>
                    @else
                        <button wire:click="deleteQuestion({{ $a->id }})"
                                wire:confirm="Delete Question A (#{{ $a->id }})? This cannot be undone."
                                class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100 transition-colors">
                            <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                            Delete A
                        </button>
                    @endif

                    @if ($asksB > 0)
                        <button wire:click="deleteQuestion({{ $b->id }})"
                                wire:confirm="Delete Question B (#{{ $b->id }})? It was asked {{ $asksB }}× in past games. This cannot be undone."
                                class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100 transition-colors">
                            <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                            Delete B (asked {{ $asksB }}×)
                        </button>
                    @else
                        <button wire:click="deleteQuestion({{ $b->id }})"
                                wire:confirm="Delete Question B (#{{ $b->id }})? This cannot be undone."
                                class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100 transition-colors">
                            <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                            Delete B
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
