@php /** @var \Illuminate\Support\Collection $notes */ @endphp

@if ($notes->isEmpty())
    <p class="text-sm text-gray-400 py-1">No internal notes yet.</p>
@else
    <div class="divide-y divide-gray-100">
        @foreach ($notes as $note)
            <div class="py-2.5">
                <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $note->note }}</p>
                <p class="text-xs text-gray-400 mt-1">
                    {{ $note->author?->name ?? 'System' }}
                    &middot;
                    {{ $note->created_at?->diffForHumans() ?? '—' }}
                </p>
            </div>
        @endforeach
    </div>
@endif
