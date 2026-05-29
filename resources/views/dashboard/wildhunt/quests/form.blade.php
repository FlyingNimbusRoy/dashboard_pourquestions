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
        $existingWaves = json_decode(old('waves', $quest->waves ?? '[]'), true) ?? [];
    @endphp

    <form method="POST"
          action="{{ isset($quest) ? route('dashboard.wildhunt.quests.update', $quest->id) : route('dashboard.wildhunt.quests.store') }}"
          class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-8"
          x-data="questForm()"
          x-init="init()"
          @submit.prevent="submitForm()">
        @csrf
        @if(isset($quest)) @method('PUT') @endif

        {{-- Identity --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Identity</h2>
            <div class="grid grid-cols-2 gap-4">

                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $quest->title ?? '') }}"
                           class="w-full border rounded py-2 px-3">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Recommended Level <span class="text-red-500">*</span></label>
                    <input type="number" name="recommended_level" min="1" max="12"
                           value="{{ old('recommended_level', $quest->recommended_level ?? 1) }}"
                           class="w-full border rounded py-2 px-3">
                    @error('recommended_level') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                    <input type="text" name="description" value="{{ old('description', $quest->description ?? '') }}"
                           class="w-full border rounded py-2 px-3">
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Intro Text
                        <span class="text-gray-400 font-normal">(story text shown before the quest begins)</span></label>
                    <textarea name="intro_text" rows="3"
                              class="w-full border rounded py-2 px-3 text-sm">{{ old('intro_text', $quest->intro_text ?? '') }}</textarea>
                    @error('intro_text') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Wave builder --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Waves</h2>
                <button type="button" @click="addWave()"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-semibold px-3 py-1.5 rounded">
                    + Add Wave
                </button>
            </div>

            @error('waves') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror

            <div class="space-y-4">
                <template x-for="(wave, wi) in waves" :key="wi">
                    <div class="border border-gray-200 rounded-lg bg-gray-50 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-gray-600">Wave <span x-text="wi + 1"></span></span>
                            <button type="button" @click="removeWave(wi)"
                                    class="text-red-400 hover:text-red-600 text-xs font-semibold">Remove wave</button>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(m, mi) in wave.monsters" :key="mi">
                                <div class="flex items-center gap-3">
                                    <select x-model="m.monster_id"
                                            class="flex-1 border rounded py-2 px-3 text-sm">
                                        <option value="">— select monster —</option>
                                        @foreach($monsters as $mon)
                                            <option value="{{ $mon->id }}">
                                                {{ $mon->icon }} {{ $mon->name }}
                                                (Lv{{ $mon->level }}){{ $mon->is_boss ? ' ★' : '' }}
                                                {{ $mon->quarry_title ? ' — ' . $mon->quarry_title : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="flex items-center gap-1">
                                        <label class="text-xs text-gray-500 whitespace-nowrap">×</label>
                                        <input type="number" x-model.number="m.count" min="1" max="10"
                                               class="w-16 border rounded py-2 px-2 text-sm text-center">
                                    </div>
                                    <button type="button" @click="removeMonster(wave, mi)"
                                            class="text-red-400 hover:text-red-600 text-xs px-1">✕</button>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="addMonster(wave)"
                                class="mt-3 text-indigo-600 hover:text-indigo-800 text-xs font-semibold">
                            + Add monster to this wave
                        </button>
                    </div>
                </template>

                <div x-show="waves.length === 0"
                     class="border-2 border-dashed border-gray-200 rounded-lg p-8 text-center text-gray-400 text-sm">
                    No waves yet — click "Add Wave" to start building the quest.
                </div>
            </div>

            {{-- Hidden field synced by Alpine on submit --}}
            <input type="hidden" name="waves" id="waves-hidden">

            {{-- Live JSON preview (collapsed) --}}
            <details class="mt-3">
                <summary class="text-xs text-gray-400 cursor-pointer hover:text-gray-600">View waves JSON</summary>
                <code class="block mt-2 text-xs font-mono bg-gray-100 border border-gray-200 rounded px-3 py-2 text-gray-500 break-all whitespace-pre-wrap"
                      x-text="wavesJson()"></code>
            </details>
        </div>

        {{-- Rewards --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Rewards</h2>
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
                    <label class="block text-gray-700 text-sm font-bold mb-2">Bonus gear <span class="text-gray-400 font-normal">(optional)</span></label>
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
                    <label class="block text-gray-700 text-sm font-bold mb-2">Bonus pet <span class="text-gray-400 font-normal">(optional)</span></label>
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
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ isset($quest) ? 'Update Quest' : 'Create Quest' }}
            </button>
            <a href="{{ route('dashboard.wildhunt.quests.index') }}" class="text-blue-500 hover:underline">Cancel</a>
        </div>

    </form>

    <script>
        function questForm() {
            const existing = @json($existingWaves);

            return {
                waves: existing.length ? existing : [],

                init() {
                    // Normalise existing waves to ensure correct structure
                    this.waves = this.waves.map(w => ({
                        monsters: (w.monsters ?? []).map(m => ({
                            monster_id: String(m.monster_id ?? ''),
                            count: parseInt(m.count) || 1,
                        })),
                    }));
                },

                addWave() {
                    this.waves.push({ monsters: [{ monster_id: '', count: 1 }] });
                },

                removeWave(i) {
                    this.waves.splice(i, 1);
                },

                addMonster(wave) {
                    wave.monsters.push({ monster_id: '', count: 1 });
                },

                removeMonster(wave, j) {
                    wave.monsters.splice(j, 1);
                },

                wavesJson() {
                    return JSON.stringify(this.waves.map((w, i) => ({
                        wave: i + 1,
                        monsters: w.monsters
                            .filter(m => m.monster_id)
                            .map(m => ({ monster_id: parseInt(m.monster_id), count: m.count })),
                    })), null, 2);
                },

                submitForm() {
                    document.getElementById('waves-hidden').value = this.wavesJson();
                    this.$el.submit();
                },
            };
        }
    </script>

@endsection
