@extends('dashboard')
@section('content')

    <div class="space-y-6">

        <div>
            <a href="{{ route('dashboard.wildhunt.gear.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Gear</a>
            <h1 class="text-3xl font-bold text-gray-800 mt-2">
                {{ isset($item) ? 'Edit ' . $item->name : 'Create Gear Item' }}
            </h1>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ isset($item) ? route('dashboard.wildhunt.gear.update', $item->id) : route('dashboard.wildhunt.gear.store') }}"
              class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-6">
            @csrf
            @if(isset($item)) @method('PUT') @endif

            {{-- Basic fields --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                    <input type="text" name="name" value="{{ old('name', $item->name ?? '') }}"
                           class="w-full border rounded py-2 px-3" required>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Slot</label>
                    <select name="slot" class="w-full border rounded py-2 px-3" required>
                        @foreach($slots as $s)
                            <option value="{{ $s }}" {{ old('slot', $item->slot ?? '') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Class <span class="font-normal text-gray-400">(blank = all)</span></label>
                    <select name="class_id" class="w-full border rounded py-2 px-3">
                        <option value="">All classes</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ old('class_id', $item->class_id ?? '') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Crest cost <span class="font-normal text-gray-400">(0 = not for sale)</span></label>
                    <input type="number" name="crest_cost" value="{{ old('crest_cost', $item->crest_cost ?? 0) }}" min="0"
                           class="w-full border rounded py-2 px-3" required>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                    <textarea name="description" rows="2"
                              class="w-full border rounded py-2 px-3" required>{{ old('description', $item->description ?? '') }}</textarea>
                </div>

            </div>

            {{-- Stat bonuses --}}
            <div class="border-t pt-5">
                <p class="text-gray-700 text-sm font-bold mb-3">Stat bonuses</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach(['hp_bonus' => 'HP', 'attack_bonus' => 'Attack', 'defense_bonus' => 'Defense', 'intelligence_bonus' => 'Intelligence', 'stealth_bonus' => 'Stealth', 'arcana_bonus' => 'Arcana', 'max_actions_bonus' => 'Max actions', 'regen' => 'Regen/turn'] as $field => $label)
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">{{ $label }}</label>
                            <input type="number" name="{{ $field }}" value="{{ old($field, $item->$field ?? 0) }}"
                                   class="w-full border rounded py-2 px-3" required>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Gauntlet loot settings --}}
            <div class="border-t pt-5 space-y-4">

                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_gauntlet_loot" value="0">
                    <input type="checkbox" name="is_gauntlet_loot" id="is_gauntlet_loot" value="1"
                           {{ old('is_gauntlet_loot', $item->is_gauntlet_loot ?? false) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300">
                    <label for="is_gauntlet_loot" class="text-gray-700 text-sm font-bold">Gauntlet loot</label>
                    <span class="text-xs text-gray-400">(not available in store)</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="gauntlet-fields">

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Gauntlet tier</label>
                        <select name="gauntlet_tier" class="w-full border rounded py-2 px-3">
                            <option value="">— none —</option>
                            @foreach(range(1, 6) as $t)
                                <option value="{{ $t }}" {{ old('gauntlet_tier', $item->gauntlet_tier ?? '') == $t ? 'selected' : '' }}>Tier {{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Quarry-specific drop</label>
                        <select name="gauntlet_quarry_id" class="w-full border rounded py-2 px-3">
                            <option value="">General tier pool</option>
                            @foreach($quarries as $q)
                                <option value="{{ $q->id }}" {{ old('gauntlet_quarry_id', $item->gauntlet_quarry_id ?? '') == $q->id ? 'selected' : '' }}>
                                    [T{{ $q->tier }}] {{ $q->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Drop weight <span class="font-normal text-gray-400">(default 100)</span></label>
                        <input type="number" name="drop_weight" value="{{ old('drop_weight', $item->drop_weight ?? 100) }}" min="1"
                               class="w-full border rounded py-2 px-3">
                    </div>

                </div>
            </div>

            {{-- Resistances builder --}}
            <div class="border-t pt-5">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Resistances
                    <span class="font-normal text-gray-400">— tick every damage type this item resists</span>
                </label>
                <input type="hidden" name="resistances" id="resistances-hidden">
                <div class="border border-gray-200 rounded-lg bg-gray-50 px-4 py-3 space-y-2">
                    <div class="flex flex-wrap gap-4">
                        @foreach(['physical','fire','poison','frost','radiant','dark'] as $dtype)
                            <label class="flex items-center gap-1.5 text-sm text-gray-700 cursor-pointer">
                                <input type="checkbox" class="res-check rounded border-gray-300" value="{{ $dtype }}">
                                {{ ucfirst($dtype) }}
                            </label>
                        @endforeach
                    </div>
                    <code id="resistances-preview" class="block text-xs font-mono text-gray-400">null</code>
                </div>
            </div>

            {{-- Damage overrides builder --}}
            <div class="border-t pt-5">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-gray-700 text-sm font-bold">
                        Damage overrides
                        <span class="font-normal text-gray-400">— convert a damage type to another (optionally per skill slug)</span>
                    </label>
                    <button type="button" id="add-override"
                            class="text-xs px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition">
                        + Add override
                    </button>
                </div>
                <input type="hidden" name="damage_overrides" id="damage-overrides-hidden">
                <div class="border border-gray-200 rounded-lg bg-gray-50 px-4 py-3 space-y-2">
                    <div id="override-rows" class="space-y-2"></div>
                    <p id="override-empty" class="text-xs text-gray-400 italic">No overrides — click "+ Add override" to add one.</p>
                    <code id="damage-overrides-preview" class="block text-xs font-mono text-gray-400">null</code>
                </div>
            </div>

            {{-- Bonus skill IDs builder --}}
            <div class="border-t pt-5">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Bonus skills
                    <span class="font-normal text-gray-400">— extra skills granted while this item is equipped</span>
                </label>
                <input type="hidden" name="bonus_skill_ids" id="bonus-skills-hidden">
                <div class="border border-gray-200 rounded-lg bg-gray-50 px-4 py-3 space-y-2">
                    <input type="text" id="skill-search" placeholder="Filter skills…"
                           class="w-full border rounded py-2 px-3 text-sm">
                    <div id="skill-list" class="grid grid-cols-1 md:grid-cols-2 gap-0.5 max-h-48 overflow-y-auto">
                        @foreach($skills as $sk)
                            <label class="skill-option flex items-center gap-2 text-sm text-gray-700 cursor-pointer px-2 py-1 rounded hover:bg-white"
                                   data-label="{{ strtolower($sk->name) }} {{ strtolower($sk->class_name ?? '') }}">
                                <input type="checkbox" class="skill-check rounded border-gray-300" value="{{ $sk->id }}">
                                <span class="font-medium">{{ $sk->icon ?? '' }} {{ $sk->name }}</span>
                                <span class="text-gray-400 text-xs ml-auto">{{ $sk->class_name ?? 'Generic' }}</span>
                            </label>
                        @endforeach
                    </div>
                    <code id="bonus-skills-preview" class="block text-xs font-mono text-gray-400">null</code>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between pt-2">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ isset($item) ? 'Update Item' : 'Save' }}
                </button>
                <a href="{{ route('dashboard.wildhunt.gear.index') }}" class="text-blue-500 hover:underline">Cancel</a>
            </div>

        </form>

    </div>

    <script>
        // -------------------------------------------------------------------------
        // Existing values passed from the server (null on create)
        // -------------------------------------------------------------------------
        const existingResistances   = @json(old('resistances',     $item->resistances    ?? null));
        const existingOverrides     = @json(old('damage_overrides', $item->damage_overrides ?? null));
        const existingBonusSkillIds = @json(old('bonus_skill_ids',  $item->bonus_skill_ids  ?? null));

        const DAMAGE_TYPES = ['physical', 'fire', 'poison', 'frost', 'radiant', 'dark'];

        // -------------------------------------------------------------------------
        // Resistances builder
        // -------------------------------------------------------------------------
        const resHidden  = document.getElementById('resistances-hidden');
        const resPreview = document.getElementById('resistances-preview');
        const resChecks  = document.querySelectorAll('.res-check');

        function syncResistances() {
            const selected = [...resChecks].filter(c => c.checked).map(c => c.value);
            const json = selected.length ? JSON.stringify(selected) : '';
            resHidden.value        = json;
            resPreview.textContent = json || 'null';
        }

        function loadResistances(raw) {
            if (!raw) return;
            let arr;
            try { arr = typeof raw === 'string' ? JSON.parse(raw) : raw; } catch { return; }
            resChecks.forEach(c => { if (arr.includes(c.value)) c.checked = true; });
        }

        resChecks.forEach(c => c.addEventListener('change', syncResistances));
        loadResistances(existingResistances);
        syncResistances();

        // -------------------------------------------------------------------------
        // Damage overrides builder
        // -------------------------------------------------------------------------
        const overrideHidden  = document.getElementById('damage-overrides-hidden');
        const overridePreview = document.getElementById('damage-overrides-preview');
        const overrideRows    = document.getElementById('override-rows');
        const overrideEmpty   = document.getElementById('override-empty');

        function buildTypeOptions(selected) {
            return DAMAGE_TYPES.map(dt =>
                `<option value="${dt}" ${dt === selected ? 'selected' : ''}>${dt.charAt(0).toUpperCase() + dt.slice(1)}</option>`
            ).join('');
        }

        function syncOverrides() {
            const rows = overrideRows.querySelectorAll('.override-row');
            overrideEmpty.style.display = rows.length ? 'none' : '';
            const arr = [...rows].map(row => ({
                skill_slug: row.querySelector('.ov-slug').value || '*',
                from:       row.querySelector('.ov-from').value,
                to:         row.querySelector('.ov-to').value,
            }));
            const json = arr.length ? JSON.stringify(arr) : '';
            overrideHidden.value        = json;
            overridePreview.textContent = json || 'null';
        }

        function addOverrideRow(slugVal = '*', fromVal = 'physical', toVal = 'fire') {
            const row = document.createElement('div');
            row.className = 'override-row flex items-center gap-2';
            row.innerHTML = `
            <div class="flex-1">
                <label class="block text-xs text-gray-500 mb-0.5">Skill slug <span class="text-gray-400">(* = all)</span></label>
                <input type="text" class="ov-slug w-full border rounded py-1.5 px-2 text-sm font-mono"
                       value="${slugVal}" placeholder="*">
            </div>
            <div class="w-28">
                <label class="block text-xs text-gray-500 mb-0.5">From</label>
                <select class="ov-from w-full border rounded py-1.5 px-2 text-sm">${buildTypeOptions(fromVal)}</select>
            </div>
            <div class="w-28">
                <label class="block text-xs text-gray-500 mb-0.5">To</label>
                <select class="ov-to w-full border rounded py-1.5 px-2 text-sm">${buildTypeOptions(toVal)}</select>
            </div>
            <button type="button" class="remove-override mt-4 text-red-400 hover:text-red-600 text-lg leading-none">&times;</button>
        `;
            row.querySelector('.remove-override').addEventListener('click', () => { row.remove(); syncOverrides(); });
            row.querySelectorAll('input, select').forEach(el => el.addEventListener('input', syncOverrides));
            overrideRows.appendChild(row);
            syncOverrides();
        }

        function loadOverrides(raw) {
            if (!raw) return;
            let arr;
            try { arr = typeof raw === 'string' ? JSON.parse(raw) : raw; } catch { return; }
            arr.forEach(o => addOverrideRow(o.skill_slug ?? '*', o.from, o.to));
        }

        document.getElementById('add-override').addEventListener('click', () => addOverrideRow());
        loadOverrides(existingOverrides);
        syncOverrides();

        // -------------------------------------------------------------------------
        // Bonus skill IDs builder
        // -------------------------------------------------------------------------
        const skillHidden  = document.getElementById('bonus-skills-hidden');
        const skillPreview = document.getElementById('bonus-skills-preview');
        const skillChecks  = document.querySelectorAll('.skill-check');

        function syncSkills() {
            const selected = [...skillChecks].filter(c => c.checked).map(c => parseInt(c.value, 10));
            const json = selected.length ? JSON.stringify(selected) : '';
            skillHidden.value        = json;
            skillPreview.textContent = json || 'null';
        }

        function loadSkills(raw) {
            if (!raw) return;
            let arr;
            try { arr = typeof raw === 'string' ? JSON.parse(raw) : raw; } catch { return; }
            skillChecks.forEach(c => { if (arr.includes(parseInt(c.value, 10))) c.checked = true; });
        }

        skillChecks.forEach(c => c.addEventListener('change', syncSkills));
        loadSkills(existingBonusSkillIds);
        syncSkills();

        // Filter skill list by typing in the search box
        document.getElementById('skill-search').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.skill-option').forEach(el => {
                el.style.display = el.dataset.label.includes(q) ? '' : 'none';
            });
        });
    </script>

@endsection