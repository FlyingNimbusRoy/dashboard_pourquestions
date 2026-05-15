@extends('dashboard')
@section('content')

    <div class="space-y-6">

        <div>
            <a href="{{ route('dashboard.wildhunt.skills.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Skills</a>
            <h1 class="text-3xl font-bold text-gray-800 mt-2">
                {{ isset($skill) ? 'Edit ' . $skill->name : 'Create Skill' }}
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
              action="{{ isset($skill) ? route('dashboard.wildhunt.skills.update', $skill->id) : route('dashboard.wildhunt.skills.store') }}"
              id="skill-form"
              class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-6">
            @csrf
            @if(isset($skill)) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Name -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                    <input type="text" name="name" value="{{ old('name', $skill->name ?? '') }}"
                           class="w-full border rounded py-2 px-3" required>
                </div>

                <!-- Slug -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $skill->slug ?? '') }}"
                           class="w-full border rounded py-2 px-3 font-mono" required>
                </div>

                <!-- Icon -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Icon <span class="font-normal text-gray-400">(emoji)</span></label>
                    <input type="text" name="icon" value="{{ old('icon', $skill->icon ?? '') }}"
                           class="w-full border rounded py-2 px-3" required>
                </div>

                <!-- Class -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Class <span class="font-normal text-gray-400">(blank = generic)</span></label>
                    <select name="class_id" class="w-full border rounded py-2 px-3">
                        <option value="">Generic (all classes)</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ old('class_id', $skill->class_id ?? '') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Type</label>
                    <select name="type" class="w-full border rounded py-2 px-3" required>
                        @foreach($types as $t)
                            <option value="{{ $t }}" {{ old('type', $skill->type ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Damage type -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Damage type</label>
                    <select name="damage_type" class="w-full border rounded py-2 px-3">
                        <option value="">— none —</option>
                        @foreach($damageTypes as $dt)
                            <option value="{{ $dt }}" {{ old('damage_type', $skill->damage_type ?? '') === $dt ? 'selected' : '' }}>{{ $dt }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Action cost -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Action cost (energy)</label>
                    <input type="number" name="action_cost" value="{{ old('action_cost', $skill->action_cost ?? 1) }}" min="0"
                           class="w-full border rounded py-2 px-3" required>
                </div>

                <!-- Value -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Value <span class="font-normal text-gray-400">(damage / heal amount)</span></label>
                    <input type="number" name="value" value="{{ old('value', $skill->value ?? 0) }}" min="0"
                           class="w-full border rounded py-2 px-3" required>
                </div>

                <!-- Level required -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Level required</label>
                    <input type="number" name="level_required" value="{{ old('level_required', $skill->level_required ?? 1) }}" min="1"
                           class="w-full border rounded py-2 px-3" required>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                    <input type="text" name="description" value="{{ old('description', $skill->description ?? '') }}"
                           class="w-full border rounded py-2 px-3" required>
                </div>

            </div>

            {{-- Extra builder --}}
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Extra effects
                    <span class="font-normal text-gray-400">— leave a number at 0 or uncheck a box to exclude it</span>
                </label>

                {{-- Hidden field that gets submitted; JS keeps it in sync --}}
                <input type="hidden" name="extra" id="extra-hidden">

                <div class="border border-gray-200 rounded-lg bg-gray-50 p-4 space-y-5">

                    {{-- Attack modifiers --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Attack</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">hits <span class="font-normal text-gray-400">(multi-hit)</span></label>
                                <input type="number" data-extra-key="hits" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">poison</label>
                                <input type="number" data-extra-key="poison" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">burn</label>
                                <input type="number" data-extra-key="burn" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">frozen</label>
                                <input type="number" data-extra-key="frozen" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">vuln</label>
                                <input type="number" data-extra-key="vuln" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">corruption</label>
                                <input type="number" data-extra-key="corruption" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">attack_down</label>
                                <input type="number" data-extra-key="attack_down" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">defense_down</label>
                                <input type="number" data-extra-key="defense_down" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                        </div>
                    </div>

                    {{-- Self-inflicted --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Self-inflicted</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">self_poison</label>
                                <input type="number" data-extra-key="self_poison" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">self_burn</label>
                                <input type="number" data-extra-key="self_burn" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                        </div>
                    </div>

                    {{-- Healing & Support --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Healing &amp; Support</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">heal <span class="font-normal text-gray-400">(self)</span></label>
                                <input type="number" data-extra-key="heal" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">friendly_heal <span class="font-normal text-gray-400">(alias)</span></label>
                                <input type="number" data-extra-key="friendly_heal" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">heal_all</label>
                                <input type="number" data-extra-key="heal_all" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">block_all</label>
                                <input type="number" data-extra-key="block_all" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                        </div>
                    </div>

                    {{-- Permanent buffs --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Permanent buffs (this hunt)</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">stealth_buff</label>
                                <input type="number" data-extra-key="stealth_buff" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">attack_buff</label>
                                <input type="number" data-extra-key="attack_buff" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-1">defense_buff</label>
                                <input type="number" data-extra-key="defense_buff" value="0" min="0" class="extra-num w-full border rounded py-2 px-3">
                            </div>
                        </div>
                    </div>

                    {{-- Boolean flags --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Boolean flags</p>
                        <div class="flex flex-wrap gap-6">
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input type="checkbox" data-extra-key="cleanse" class="extra-bool rounded border-gray-300">
                                <span>cleanse <span class="text-gray-400">(remove own corruption)</span></span>
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input type="checkbox" data-extra-key="cleanse_all" class="extra-bool rounded border-gray-300">
                                <span>cleanse_all <span class="text-gray-400">(remove all players' corruption)</span></span>
                            </label>
                        </div>
                    </div>

                    {{-- Live preview --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Preview</p>
                        <code id="extra-preview" class="block text-xs font-mono bg-white border border-gray-200 rounded px-3 py-2 text-gray-500 min-h-[2rem] break-all">
                            null
                        </code>
                    </div>

                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ isset($skill) ? 'Update Skill' : 'Save' }}
                </button>
                <a href="{{ route('dashboard.wildhunt.skills.index') }}" class="text-blue-500 hover:underline">Cancel</a>
            </div>

        </form>

    </div>

    <script>
        // -----------------------------------------------------------------------
        // Extra builder — reads all number inputs and checkboxes, serialises to
        // JSON and writes to the hidden <input name="extra"> before submit.
        // -----------------------------------------------------------------------

        // Existing extra value passed from the server (null on create)
        const existingExtra = @json(old('extra', $skill->extra ?? null));

        const hiddenField  = document.getElementById('extra-hidden');
        const previewEl    = document.getElementById('extra-preview');
        const numInputs    = document.querySelectorAll('input.extra-num');
        const boolInputs   = document.querySelectorAll('input.extra-bool');

        // Build the JSON object from the current state of all builder fields
        function buildExtra() {
            const obj = {};

            numInputs.forEach(input => {
                const val = parseInt(input.value, 10);
                if (!isNaN(val) && val > 0) {
                    obj[input.dataset.extraKey] = val;
                }
            });

            boolInputs.forEach(input => {
                if (input.checked) {
                    obj[input.dataset.extraKey] = true;
                }
            });

            return Object.keys(obj).length > 0 ? obj : null;
        }

        // Sync the hidden field and the preview element
        function syncExtra() {
            const obj = buildExtra();
            const json = obj ? JSON.stringify(obj) : '';
            hiddenField.value = json;
            previewEl.textContent = json || 'null';
        }

        // Populate builder fields from an existing JSON string (edit page)
        function loadExtra(raw) {
            if (!raw) return;

            let parsed;
            try {
                parsed = typeof raw === 'string' ? JSON.parse(raw) : raw;
            } catch (e) {
                // If the stored value is somehow malformed, leave all fields at defaults
                return;
            }

            numInputs.forEach(input => {
                const key = input.dataset.extraKey;
                if (key in parsed && typeof parsed[key] === 'number') {
                    input.value = parsed[key];
                }
            });

            boolInputs.forEach(input => {
                const key = input.dataset.extraKey;
                if (parsed[key] === true) {
                    input.checked = true;
                }
            });
        }

        // Wire up change listeners
        numInputs.forEach(input  => input.addEventListener('input',  syncExtra));
        boolInputs.forEach(input => input.addEventListener('change', syncExtra));

        // Load existing data on edit pages, then do an initial sync
        loadExtra(existingExtra);
        syncExtra();
    </script>

@endsection