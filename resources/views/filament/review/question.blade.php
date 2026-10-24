@php
    /** @var \App\Models\Question $question */
    $percent = $total > 0 ? round($done / $total * 100) : 0;
@endphp

<div wire:key="question-{{ $question->id }}"
     style="display: flex; flex-direction: column; gap: 1rem;"
     x-data
     x-init="
        if (! window.__reviewShortcutGuard) {
            window.__reviewShortcutGuard = true;
            document.addEventListener('keydown', (e) => {
                if (! document.querySelector('.fi-modal-open')) return;
                if (e.ctrlKey || e.metaKey || e.altKey) return;
                if (! /^[adsADS1-5]$/.test(e.key)) return;
                if (e.target.closest && e.target.closest('input, textarea, select, [contenteditable]')) return;
                e.stopPropagation();
            }, true);
        }
     ">
    <div style="height: 6px; border-radius: 9999px; background: rgba(168, 85, 247, 0.15); overflow: hidden;">
        <div style="height: 100%; width: {{ $percent }}%; background: rgb(168, 85, 247);"></div>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
        <x-filament::badge color="gray">#{{ $question->id }}</x-filament::badge>
        <x-filament::badge>{{ $question->category?->name ?? 'No category' }}</x-filament::badge>
        @if ($question->gamepack)
            <x-filament::badge color="info">{{ $question->gamepack->name }}</x-filament::badge>
        @endif
        @if ($question->is_nsfw)
            <x-filament::badge color="danger">NSFW</x-filament::badge>
        @endif
        <x-filament::badge color="warning">Current difficulty {{ $question->difficulty }}</x-filament::badge>
        <x-filament::badge color="gray">
            {{ $question->wasEverValidated() ? 'Validated '.$question->last_validated->diffForHumans() : 'Never validated' }}
        </x-filament::badge>
    </div>

    <div style="font-size: 1.5rem; font-weight: 600; line-height: 1.3;">{{ $question->vraag }}</div>

    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
        @forelse ($question->answers->sortBy('id') as $answer)
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                @if ($answer->is_correct)
                    <x-filament::badge color="success" icon="heroicon-m-check">{{ $answer->answer }}</x-filament::badge>
                @else
                    <x-filament::badge color="gray">{{ $answer->answer }}</x-filament::badge>
                @endif
            </div>
        @empty
            <x-filament::badge color="danger">This question has no answers</x-filament::badge>
        @endforelse
    </div>

    @if (filled($question->trivia))
        <div style="font-size: 0.875rem; opacity: 0.7;">{{ $question->trivia }}</div>
    @endif

    <div style="font-size: 0.75rem; opacity: 0.6;">Shortcuts: A approve · D deny · S skip · 1-5 difficulty</div>
</div>
