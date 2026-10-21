<x-filament-panels::page>
    <p class="text-sm" style="color:#57466e">
        The dashboard runs on a local copy. Every insert, update and delete on game content (questions, answers, categories, gamepacks, characters, modifiers, achievements, games, gamepack grants) is queued below. Download the SQL, review it and apply it to the live database yourself. Nothing is ever executed against live from this page. Inserts keep their local id and are skipped on live if that id is already taken (a warning is returned).
    </p>
    {{ $this->table }}

    @php($batches = $this->getBatches())
    @if ($batches->isNotEmpty())
        <x-filament::section heading="Exported batches" collapsible collapsed>
            <table class="w-full text-sm">
                <thead><tr class="text-left"><th>Exported</th><th>By</th><th>Changes</th><th>File</th><th></th></tr></thead>
                <tbody>
                @foreach ($batches as $b)
                    <tr wire:key="batch-{{ $b->id }}">
                        <td>{{ $b->exported_at?->format('d-m-Y H:i') }}</td>
                        <td>{{ $b->user?->name ?? 'console' }}</td>
                        <td>{{ $b->change_count }}</td>
                        <td>{{ $b->filename }}</td>
                        <td><x-filament::link tag="button" wire:click="redownload({{ $b->id }})" icon="heroicon-o-arrow-down-tray">Download again</x-filament::link></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </x-filament::section>
    @endif
</x-filament-panels::page>
