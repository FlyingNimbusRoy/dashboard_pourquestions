@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ isset($category) ? 'Edit' : 'Create' }} Category</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ isset($category) ? route('categories.update', $category) : route('categories.store') }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-6">
        @csrf
        @if(isset($category))
            @method('PUT')
        @endif

        <!-- Name -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
            <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" class="w-full border rounded py-2 px-3">
        </div>

        <!-- Description -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
            <input type="text" name="description" value="{{ old('description', $category->description ?? '') }}" class="w-full border rounded py-2 px-3">
        </div>

        <!-- Icon -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Icon (e.g., FontAwesome)</label>
            <input type="text" name="icon" id="icon-input" value="{{ old('icon', $category->icon ?? '') }}" class="w-full border rounded py-2 px-3">
            <div class="mt-2">
                <i id="icon-preview" class="{{ old('icon', $category->icon ?? '') }} text-xl"></i>
            </div>
        </div>

        <!-- Color -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Color</label>
            <input type="color" name="color" id="color-input" value="{{ old('color', $category->color ?? '#000000') }}" class="w-16 h-10 border rounded">
            <span id="color-value" class="ml-2">{{ old('color', $category->color ?? '#000000') }}</span>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Save
            </button>
            <a href="{{ route('categories.index') }}" class="text-blue-500 hover:underline">Cancel</a>
        </div>
    </form>

    <script>
        // Icon preview
        const iconInput = document.getElementById('icon-input');
        const iconPreview = document.getElementById('icon-preview');

        iconInput.addEventListener('input', function () {
            iconPreview.className = iconInput.value + ' text-xl';
        });

        // Color picker
        const colorInput = document.getElementById('color-input');
        const colorValue = document.getElementById('color-value');

        colorInput.addEventListener('input', function () {
            colorValue.textContent = colorInput.value;
        });
    </script>
@endsection
