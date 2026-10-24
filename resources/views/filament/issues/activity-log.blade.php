@if($activities->isEmpty())
    <p class="text-sm text-gray-400">No activity yet.</p>
@else
    <ol class="space-y-2">
        @foreach($activities as $a)
            <li class="flex items-start gap-3 text-sm">
                <span class="mt-0.5 inline-flex items-center rounded-full bg-violet-100 px-2 py-0.5 text-xs font-medium text-violet-800">{{ $a->action }}</span>
                @if($a->user)<span class="font-medium text-gray-700">{{ $a->user->name }}</span>@endif
                @if($a->from_value || $a->to_value)
                    <span class="text-gray-500">{{ $a->from_value }} → {{ $a->to_value }}</span>
                @endif
                @if($a->note)<span class="text-gray-600">{{ $a->note }}</span>@endif
                <span class="ml-auto text-xs text-gray-400">{{ $a->created_at?->diffForHumans() }}</span>
            </li>
        @endforeach
    </ol>
@endif
