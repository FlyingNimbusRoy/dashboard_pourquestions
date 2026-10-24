<dl style="display: grid; grid-template-columns: auto 1fr; gap: 0.5rem 1rem; font-size: 0.875rem; margin: 0;">
    <dt style="opacity: 0.7;">Times asked</dt>
    <dd style="margin: 0; font-weight: 600;">
        {{ $timesAsked }}
        @if ($lastGameId)
            <span style="font-weight: 400; opacity: 0.7;">
                (last in
                @if ($lastGameUrl)<a href="{{ $lastGameUrl }}" style="text-decoration: underline;">game #{{ $lastGameId }}</a>@else game #{{ $lastGameId }}@endif
                @if ($lastAskedAt), {{ $lastAskedAt->toFormattedDateString() }}@endif)
            </span>
        @endif
    </dd>

    <dt style="opacity: 0.7;">Last validated</dt>
    <dd style="margin: 0;">
        @if ($question->wasEverValidated())
            {{ $question->last_validated->toFormattedDateString() }}
            @if ($question->isValidationDue())<x-filament::badge color="warning" size="sm" style="display: inline-flex;">due</x-filament::badge>@endif
        @else
            Never
        @endif
    </dd>

    <dt style="opacity: 0.7;">Difficulty</dt>
    <dd style="margin: 0;">{{ $question->difficulty }} / 5</dd>

    <dt style="opacity: 0.7;">Denied</dt>
    <dd style="margin: 0;">{{ $openDenials ? 'Yes, waiting for a fix' : 'No' }}</dd>

    <dt style="opacity: 0.7;">Open issues</dt>
    <dd style="margin: 0;">
        @if ($openIssues)
            <x-filament::badge color="danger" size="sm" style="display: inline-flex;">{{ $openIssues }}</x-filament::badge>
        @else
            0
        @endif
    </dd>

    <dt style="opacity: 0.7;">Open duplicates</dt>
    <dd style="margin: 0;">
        @if ($openDuplicates)
            <a href="{{ \App\Filament\Resources\QuestionSimilarities\QuestionSimilarityResource::getUrl('index', ['tableSearch' => (string) $question->id]) }}" style="text-decoration: underline;">{{ $openDuplicates }}</a>
        @else
            0
        @endif
    </dd>

    <dt style="opacity: 0.7;">Last change</dt>
    <dd style="margin: 0;">{{ $lastChange ? $lastChange->created_at->diffForHumans() : 'None recorded' }}</dd>
</dl>
