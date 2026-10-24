@php
    $assignee = $record->assignee_id ? \App\Models\User::find($record->assignee_id) : null;
    $subjectUrl = match ($record->subject_type) {
        'question' => $record->subject_id ? \App\Filament\Resources\Questions\QuestionResource::getUrl('edit', ['record' => $record->subject_id]) : null,
        'minigame' => $record->subject_id ? \App\Filament\Resources\Minigames\MinigameResource::getUrl('edit', ['record' => $record->subject_id]) : null,
        'modifier' => $record->subject_id ? \App\Filament\Resources\Modifiers\ModifierResource::getUrl('edit', ['record' => $record->subject_id]) : null,
        default    => null,
    };
@endphp
<dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
    <div><dt class="text-gray-500">Type</dt><dd class="font-medium">{{ $record->type }}</dd></div>
    <div><dt class="text-gray-500">Status</dt><dd class="font-medium">{{ $record->status }}</dd></div>
    <div><dt class="text-gray-500">Subject</dt><dd>
        @if($subjectUrl)
            <a href="{{ $subjectUrl }}" class="text-purple-600 underline">{{ $record->subject_type }} #{{ $record->subject_id }}</a>
        @else
            {{ $record->subject_type }}@if($record->subject_id) #{{ $record->subject_id }}@endif
        @endif
    </dd></div>
    <div><dt class="text-gray-500">Reason code</dt><dd class="font-mono text-xs">{{ $record->reason_code }}</dd></div>
    <div class="col-span-2"><dt class="text-gray-500">Summary</dt><dd>{{ $record->reason_summary }}</dd></div>
    @if($record->metric_snapshot)
    <div class="col-span-2"><dt class="text-gray-500 mb-1">Metrics</dt><dd><pre class="text-xs bg-gray-50 rounded p-2 overflow-x-auto">{{ json_encode($record->metric_snapshot, JSON_PRETTY_PRINT) }}</pre></dd></div>
    @endif
    <div><dt class="text-gray-500">Assignee</dt><dd>{{ $assignee?->name ?? 'Unassigned' }}</dd></div>
    <div><dt class="text-gray-500">Raised</dt><dd>{{ $record->created_at?->diffForHumans() }}</dd></div>
    @if($record->resolved_at)<div class="col-span-2"><dt class="text-gray-500">Resolved</dt><dd>{{ $record->resolved_at->diffForHumans() }}</dd></div>@endif
</dl>
