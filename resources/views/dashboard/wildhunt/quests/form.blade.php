@extends('dashboard')
@section('content')

    <h1 class="text-2xl font-bold mb-4">
        {{ isset($quest) ? 'Edit ' . $quest->title : 'New Quest' }}
    </h1>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    @php
        $existingWaves  = json_decode(old('waves', $quest->waves ?? '[]'), true) ?? [];
        // Group monsters by quarry for optgroups
        $monsterGroups  = collect($monsters)->groupBy(fn($m) => $m->quarry_title ?? 'Unassigned');
    @endphp

    <form id="quest-form"
          method="POST"
          action="{{ isset($quest) ? route('dashboard.wildhunt.quests.update', $quest->id) : route('dashboard.wildhunt.quests.store') }}"
          class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-8">
        @csrf
        @if(isset($quest)) @method('PUT') @endif

        {{-- Identity --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Identity</h2>
            <div class="grid grid-cols-2 gap-4">

                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $quest->title ?? '') }}"
                           class="w-full border rounded py-2 px-3" required>
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Recommended Level <span class="text-red-500">*</span></label>
                    <input type="number" name="recommended_level" min="1" max="12"
                           value="{{ old('recommended_level', $quest->recommended_level ?? 1) }}"
                           class="w-full border rounded py-2 px-3" required>
                    @error('recommended_level') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Short description</label>
                    <input type="text" name="description" value="{{ old('description', $quest->description ?? '') }}"
                           class="w-full border rounded py-2 px-3" placeholder="Shown on the challenge board">
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Intro text
                        <span class="text-gray-400 font-normal">(story shown to players before they start)</span></label>
                    <textarea name="intro_text" rows="3"
                              class="w-full border rounded py-2 px-3 text-sm"
                              placeholder="Deep in the Ashen Wastes, something stirs…">{{ old('intro_text', $quest->intro_text ?? '') }}</textarea>
                    @error('intro_text') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Wave builder --}}
        <div x-data="waveBuilder()" x-init="init()">

            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Waves</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Build the quest wave by wave. Players fight each wave in order.</p>
                </div>
                <button type="button" @click="addWave()"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm">
                    + Add Wave
                </button>
            </div>

            @error('waves') <p class="text-red-500 text-xs mb-3">{{ $message }}</p> @enderror

            {{-- Wave cards --}}
            <div class="space-y-4" id="wave-list">
                <template x-for="(wave, wi) in waves" :key="wi">
                    <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">

                        {{-- Wave header --}}
                        <div class="flex items-center justify-between px-4 py-3 bg-gray-100 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-bold"
                                      x-text="wi + 1"></span>
                                <span class="text-sm font-semibold text-gray-700">Wave <span x-text="wi + 1"></span></span>
                                <span class="text-xs text-gray-400"
                                      x-text="wave.monsters.filter(m => m.monster_id).length + ' monster type' + (wave.monsters.filter(m => m.monster_id).length !== 1 ? 's' : '')"></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="moveUp(wi)" :disabled="wi === 0"
                                        class="p-1.5 rounded hover:bg-gray-200 disabled:opacity-30 disabled:cursor-not-allowed text-gray-500"
                                        title="Move wave up">↑</button>
                                <button type="button" @click="moveDown(wi)" :disabled="wi === waves.length - 1"
                                        class="p-1.5 rounded hover:bg-gray-200 disabled:opacity-30 disabled:cursor-not-allowed text-gray-500"
                                        title="Move wave down">↓</button>
                                <button type="button" @click="removeWave(wi)"
                                        class="ml-2 px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 rounded border border-red-200">
                                    Remove wave
                                </button>
                            </div>
                        </div>

                        {{-- Monster rows --}}
                        <div class="p-4 space-y-3 bg-white">

                            <template x-for="(m, mi) in wave.monsters" :key="mi">
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-100">

                                    {{-- Monster select --}}
                                    <select x-model="m.monster_id"
                                            class="flex-1 border border-gray-200 rounded-lg py-2 px-3 text-sm bg-white focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                                        <option value="">— pick a monster —</option>
                                        @foreach($monsterGroups as $quarryName => $quarryMonsters)
                                            <optgroup label="{{ $quarryName }}">
                                                @foreach($quarryMonsters as $mon)
                                                    <option value="{{ $mon->id }}">
                                                        {{ $mon->icon }}
                                                        {{ $mon->name }}
                                                        (Lv{{ $mon->level }}){{ $mon->is_boss ? ' ★ Boss' : '' }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>

                                    {{-- Count --}}
                                    <div class="flex items-center gap-1 shrink-0">
                                        <span class="text-xs text-gray-500 mr-1">×</span>
                                        <button type="button"
                                                @click="m.count = Math.max(1, m.count - 1)"
                                                class="w-7 h-7 rounded border border-gray-300 bg-white hover:bg-gray-100 text-gray-600 font-bold text-sm flex items-center justify-center">−</button>
                                        <span class="w-8 text-center text-sm font-semibold text-gray-700" x-text="m.count"></span>
                                        <button type="button"
                                                @click="m.count = Math.min(10, m.count + 1)"
                                                class="w-7 h-7 rounded border border-gray-300 bg-white hover:bg-gray-100 text-gray-600 font-bold text-sm flex items-center justify-center">+</button>
                                    </div>

                                    {{-- Remove monster --}}
                                    <button type="button" @click="removeMonster(wave, mi)"
                                            class="shrink-0 w-7 h-7 flex items-center justify-center rounded-full hover:bg-red-100 text-red-400 hover:text-red-600 text-sm font-bold"
                                            title="Remove monster">✕</button>
                                </div>
                            </template>

                            <button type="button" @click="addMonster(wave)"
                                    class="w-full py-2 text-sm text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 border border-dashed border-indigo-300 rounded-lg font-medium transition">
                                + Add monster to this wave
                            </button>

                            {{-- Story frame for this wave --}}
                            <div class="mt-3 border-t border-gray-100 pt-3">
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" x-model="wave.story_before.enabled"
                                           class="rounded border-gray-300">
                                    <span class="text-xs font-semibold text-gray-600">Story frame before this wave</span>
                                </label>
                                <div x-show="wave.story_before.enabled" class="space-y-2 mt-2">
                                    <input type="text" x-model="wave.story_before.title"
                                           placeholder="Title (optional)"
                                           class="w-full border border-gray-200 rounded-lg py-1.5 px-3 text-sm bg-white">
                                    <textarea x-model="wave.story_before.text" rows="3"
                                              placeholder="Story text shown to players before this wave begins…"
                                              class="w-full border border-gray-200 rounded-lg py-1.5 px-3 text-sm bg-white resize-y"></textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                </template>

                {{-- Empty state --}}
                <div x-show="waves.length === 0"
                     class="border-2 border-dashed border-gray-200 rounded-xl p-10 text-center">
                    <p class="text-gray-400 text-sm mb-3">No waves yet.</p>
                    <button type="button" @click="addWave()"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                        + Add your first wave
                    </button>
                </div>
            </div>

            {{-- Hidden field — populated on submit by the plain JS listener below --}}
            <input type="hidden" name="waves" id="waves-hidden">

        </div>

        {{-- Rewards --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Rewards
                <span class="text-gray-400 font-normal text-xs normal-case ml-1">(granted on completion)</span>
            </h2>
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Crests <span class="text-red-500">*</span></label>
                    <input type="number" name="reward_crests"
                           value="{{ old('reward_crests', $quest->reward_crests ?? 0) }}" min="0"
                           class="w-full border rounded py-2 px-3">
                    @error('reward_crests') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">XP <span class="text-red-500">*</span></label>
                    <input type="number" name="reward_xp"
                           value="{{ old('reward_xp', $quest->reward_xp ?? 0) }}" min="0"
                           class="w-full border rounded py-2 px-3">
                    @error('reward_xp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Bonus gear
                        <span class="text-gray-400 font-normal">(optional)</span></label>
                    <select name="reward_gear_id" class="w-full border rounded py-2 px-3">
                        <option value="">— no gear reward —</option>
                        @foreach($gear as $g)
                            <option value="{{ $g->id }}"
                                    {{ old('reward_gear_id', $quest->reward_gear_id ?? '') == $g->id ? 'selected' : '' }}>
                                {{ $g->name }} ({{ $g->slot }})
                            </option>
                        @endforeach
                    </select>
                    @error('reward_gear_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Companion pet
                        <span class="text-gray-400 font-normal">(optional)</span></label>
                    <select name="reward_pet_slug" class="w-full border rounded py-2 px-3">
                        <option value="">— no pet reward —</option>
                        @foreach($pets as $p)
                            <option value="{{ $p->slug }}"
                                    {{ old('reward_pet_slug', $quest->reward_pet_slug ?? '') === $p->slug ? 'selected' : '' }}>
                                {{ $p->icon }} {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('reward_pet_slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg">
                {{ isset($quest) ? 'Save Changes' : 'Create Quest' }}
            </button>
            <a href="{{ route('dashboard.wildhunt.quests.index') }}" class="text-blue-500 hover:underline">Cancel</a>
        </div>

    </form>

    <script>
        // ── Wave builder ──────────────────────────────────────────────────────
        // Exposed globally so the form submit listener can read it.
        let _waveBuilder = null;

        function waveBuilder() {
            const existing = @json($existingWaves);

            return {
                waves: [],

                init() {
                    _waveBuilder = this;

                    this.waves = (existing || []).map(w => ({
                        monsters: (w.monsters || []).map(m => ({
                            monster_id: String(m.monster_id || ''),
                            count: parseInt(m.count) || 1,
                        })),
                        story_before: w.story_before
                            ? { enabled: true, title: w.story_before.title || '', text: w.story_before.text || '' }
                            : { enabled: false, title: '', text: '' },
                    }));
                },

                addWave() {
                    this.waves.push({ monsters: [], story_before: { enabled: false, title: '', text: '' } });
                },

                removeWave(i) {
                    if (confirm('Remove wave ' + (i + 1) + '?')) {
                        this.waves.splice(i, 1);
                    }
                },

                moveUp(i) {
                    if (i > 0) {
                        const tmp = this.waves[i - 1];
                        this.waves[i - 1] = this.waves[i];
                        this.waves[i] = tmp;
                        // Alpine needs reactivity nudge when swapping by index
                        this.waves = [...this.waves];
                    }
                },

                moveDown(i) {
                    if (i < this.waves.length - 1) {
                        const tmp = this.waves[i + 1];
                        this.waves[i + 1] = this.waves[i];
                        this.waves[i] = tmp;
                        this.waves = [...this.waves];
                    }
                },

                addMonster(wave) {
                    wave.monsters.push({ monster_id: '', count: 1 });
                },

                removeMonster(wave, j) {
                    wave.monsters.splice(j, 1);
                },

                toJson() {
                    return JSON.stringify(
                        this.waves.map((w, i) => ({
                            wave: i + 1,
                            monsters: w.monsters
                                .filter(m => m.monster_id !== '')
                                .map(m => ({
                                    monster_id: parseInt(m.monster_id),
                                    count: m.count,
                                })),
                            story_before: (w.story_before?.enabled && w.story_before?.text)
                                ? { title: w.story_before.title || null, text: w.story_before.text }
                                : null,
                        }))
                    );
                },
            };
        }

        // Populate the hidden waves field right before form submission.
        // Using a plain submit event listener avoids all Alpine.js submit quirks.
        document.getElementById('quest-form').addEventListener('submit', function () {
            if (_waveBuilder) {
                document.getElementById('waves-hidden').value = _waveBuilder.toJson();
            }
        });
    </script>

@endsection
