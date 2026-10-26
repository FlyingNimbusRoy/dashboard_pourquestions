<div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
    @if(count($images) === 0)
        <p class="text-sm text-gray-500 dark:text-gray-400">No images uploaded yet. Use the "Upload image" button above.</p>
    @else
        <div class="flex flex-wrap gap-4">
            @foreach($images as $filename)
                <div class="flex flex-col items-center gap-1 w-32">
                    <div class="w-32 h-32 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden bg-gray-50 dark:bg-gray-800">
                        <img
                            src="{{ asset('img/characters/' . $filename) }}"
                            alt="{{ $filename }}"
                            class="w-full h-full object-cover"
                        >
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate w-full text-center" title="{{ $filename }}">
                        {{ $filename }}
                    </p>
                    <button
                        wire:click="deleteImage('{{ $filename }}')"
                        wire:confirm="Delete {{ $filename }}?"
                        class="w-full rounded text-xs bg-red-600 hover:bg-red-700 text-white px-2 py-1 transition"
                    >
                        Delete
                    </button>
                </div>
            @endforeach
        </div>
    @endif
</div>
