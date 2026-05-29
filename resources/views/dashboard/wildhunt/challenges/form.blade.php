@extends('dashboard')
@section('content')

    <h1 class="text-2xl font-bold mb-4">
        {{ isset($challenge) ? 'Edit Challenge' : 'New Challenge' }}
    </h1>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Pass existing objective config to Alpine --}}
    @php
        $existingConfig   = old('objective_config', $challenge->objective_config ?? '{}');
        $existingObjType  = old('objective_type',   $challenge->objective_type  ?? 'complete_gauntlet');
        $parsedConfig     = json_decode($existingConfig, true) ?? [];
    @endphp

    <form method="POST"
          action="{{ isset($challenge) ? route('dashboard.wildhunt.challenges.update', $challenge->id) : route('dashboard.wildhunt.challenges.store') }}"
          class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-8"
          x-data="challengeForm()"
          x-init="init()">
        @csrf
        @if(isset($challenge)) @method('PUT') @endif

        {{-- Identity --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Identity</h2>
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Type <span class="text-red-500">*</span></label>
                    <select name="type" class="w-full border rounded py-2 px-3">
                        @foreach(['daily','weekly','monthly'] as $t)
                            <option value="{{ $t }}" {{ old('type', $challenge->type ?? 'daily') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                    @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Icon <span class="text-gray-400 font-normal">(emoji, optional)</span></label>
                    <input type="text" name="icon" value="{{ old('icon', $challenge->icon ?? '') }}"
                           class="w-full border rounded py-2 px-3 text-xl" maxlength="10">
                    @error('icon') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $challenge->title ?? '') }}"
                           class="w-full border rounded py-2 px-3">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Description <span class="text-gray-400 font-normal">(shown to players)</span></label>
                    <textarea name="description" rows="2"
                              class="w-full border rounded py-2 px-3 text-sm">{{ old('description', $challenge->description ?? '') }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Active window --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Active Window</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Active from <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="active_from"
                           value="{{ old('active_from', isset($challenge) ? \Carbon\Carbon::parse($challenge->active_from)->format('Y-m-d\TH:i') : '') }}"
                           class="w-full border rounded py-2 px-3">
                    @error('active_from') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Active until <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="active_until"
                           value="{{ old('active_until', isset($challenge) ? \Carbon\Carbon::parse($challenge->active_until)->format('Y-m-d\TH:i') : '') }}"
                           class="w-full border rounded py-2 px-3">
                    @error('active_until') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Objective --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Objective</h2>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Objective type <span class="text-red-500">*</span></label>
                    <select name="objective_type" x-model="objectiveType" class="w-full border rounded py-2 px-3">
                        @foreach($objectiveTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('objective_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Target <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(how many times)</span></label>
                    <input type="number" name="objective_target" min="1"
                           value="{{ old('objective_target', $challenge->objective_target ?? 1) }}"
                           class="w-full border rounded py-2 px-3">
                    @error('objective_target') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Dynamic config panels --}}
            <div class="border border-gray-200 rounded-lg bg-gray-50 p-4 space-y-4">

                {{-- complete_gauntlet --}}
                <div x-show="objectiveType === 'complete_gauntlet'">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Gauntlet options</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tier <span class="text-gray-400 font-normal">(blank = any tier)</span></label>
                            <select x-model="config.tier" class="w-full border rounded py-2 px-3">
                                <option value="">Any tier</option>
                                @foreach(range(1,6) as $t)
                                    <option value="{{ $t }}">Tier {{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- complete_hunt --}}
                <div x-show="objectiveType === 'complete_hunt'">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Hunt options</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tier <span class="text-gray-400 font-normal">(blank = any)</span></label>
                            <select x-model="config.tier" class="w-full border rounded py-2 px-3">
                                <option value="">Any tier</option>
                                @foreach(range(1,6) as $t)
                                    <option value="{{ $t }}">Tier {{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Specific quarry <span class="text-gray-400 font-normal">(optional)</span></label>
                            <select x-model="config.quarry_id" class="w-full border rounded py-2 px-3">
                                <option value="">Any quarry</option>
                                @foreach($quarries as $q)
                                    <option value="{{ $q->id }}">[T{{ $q->tier }}] {{ $q->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- slay_boss --}}
                <div x-show="objectiveType === 'slay_boss'">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Boss target</p>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Boss monster <span class="text-red-500">*</span></label>
                        <select x-model="config.monster_id" class="w-full border rounded py-2 px-3">
                            <option value="">— select boss —</option>
                            @foreach($bosses as $b)
                                <option value="{{ $b->id }}">
                                    {{ $b->icon }} {{ $b->name }}{{ $b->quarry_title ? ' (' . $b->quarry_title . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- earn_crests --}}
                <div x-show="objectiveType === 'earn_crests'">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Crest target</p>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Amount <span class="text-gray-400 font-normal">(use Target field above)</span></label>
                        <p class="text-sm text-gray-500">Set the crest amount in the Target field. Progress tracks crests earned during the active window.</p>
                    </div>
                </div>

                {{-- complete_quest --}}
                <div x-show="objectiveType === 'complete_quest'">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Quest target</p>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Quest <span class="text-red-500">*</span></label>
                        <select x-model="config.quest_id" class="w-full border rounded py-2 px-3">
                            <option value="">— select quest —</option>
                            @foreach($quests as $q)
                                <option value="{{ $q->id }}">{{ $q->title }} (rec. Lv{{ $q->recommended_level }})</option>
                            @endforeach
                        </select>
                        @if($quests->isEmpty())
                            <p class="text-yellow-600 text-xs mt-1">No quests yet — <a href="{{ route('dashboard.wildhunt.quests.create') }}" class="underline">create one first</a>.</p>
                        @endif
                    </div>
                </div>

                {{-- Config preview / hidden field --}}
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Config JSON preview</p>
                    <code class="block text-xs font-mono bg-white border border-gray-200 rounded px-3 py-2 text-gray-500 break-all"
                          x-text="configJson()"></code>
                    <input type="hidden" name="objective_config" :value="configJson()">
                </div>

            </div>
            @error('objective_config') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Rewards --}}
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-3">Rewards</h2>
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Crests <span class="text-red-500">*</span></label>
                    <input type="number" name="reward_crests"
                           value="{{ old('reward_crests', $challenge->reward_crests ?? 0) }}" min="0"
                           class="w-full border rounded py-2 px-3">
                    @error('reward_crests') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">XP <span class="text-red-500">*</span></label>
                    <input type="number" name="reward_xp"
                           value="{{ old('reward_xp', $challenge->reward_xp ?? 0) }}" min="0"
                           class="w-full border rounded py-2 px-3">
                    @error('reward_xp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Bonus gear <span class="text-gray-400 font-normal">(optional)</span></label>
                    <select name="reward_gear_id" class="w-full border rounded py-2 px-3">
                        <option value="">— no gear reward —</option>
                        @foreach($gear as $g)
                            <option value="{{ $g->id }}"
                                    {{ old('reward_gear_id', $challenge->reward_gear_id ?? '') == $g->id ? 'selected' : '' }}>
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
                                    {{ old('reward_pet_slug', $challenge->reward_pet_slug ?? '') === $p->slug ? 'selected' : '' }}>
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
                {{ isset($challenge) ? 'Update Challenge' : 'Create Challenge' }}
            </button>
            <a href="{{ route('dashboard.wildhunt.challenges.index') }}" class="text-blue-500 hover:underline">Cancel</a>
        </div>

    </form>

    <script>
        function challengeForm() {
            const existingType   = @json($existingObjType);
            const existingConfig = @json($parsedConfig);

            return {
                objectiveType: existingType,
                config: {
                    tier:       existingConfig.tier       ?? '',
                    quarry_id:  existingConfig.quarry_id  ?? '',
                    monster_id: existingConfig.monster_id ?? '',
                    quest_id:   existingConfig.quest_id   ?? '',
                },

                init() {
                    this.$watch('objectiveType', () => this.resetConfig());
                },

                resetConfig() {
                    this.config = { tier: '', quarry_id: '', monster_id: '', quest_id: '' };
                },

                configJson() {
                    const t = this.objectiveType;
                    const c = this.config;
                    if (t === 'complete_gauntlet') return JSON.stringify({ tier: c.tier ? parseInt(c.tier) : null });
                    if (t === 'complete_hunt')     return JSON.stringify({ tier: c.tier ? parseInt(c.tier) : null, quarry_id: c.quarry_id ? parseInt(c.quarry_id) : null });
                    if (t === 'slay_boss')         return JSON.stringify({ monster_id: c.monster_id ? parseInt(c.monster_id) : null });
                    if (t === 'earn_crests')       return JSON.stringify({});
                    if (t === 'complete_quest')    return JSON.stringify({ quest_id: c.quest_id ? parseInt(c.quest_id) : null });
                    return '{}';
                },
            };
        }
    </script>

@endsection
