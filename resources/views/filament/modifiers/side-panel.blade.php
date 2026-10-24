<dl class="space-y-3 text-sm">
    <div>
        <dt class="text-gray-500">Games with modifier active</dt>
        <dd class="text-2xl font-bold text-gray-950 mt-0.5">
            {{ $appearances > 0 ? number_format($appearances) : '—' }}
        </dd>
        @if ($appearances === 0)
            <p class="text-xs text-gray-400">No tracked sessions yet.</p>
        @endif
    </div>
    <div>
        <dt class="text-gray-500">Open issues</dt>
        <dd class="mt-0.5">
            @if ($openIssues > 0)
                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                    {{ $openIssues }} open
                </span>
            @else
                <span class="text-gray-400 text-xs">None</span>
            @endif
        </dd>
    </div>
</dl>
