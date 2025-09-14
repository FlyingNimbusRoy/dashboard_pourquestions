@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Import Questions</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('questions.import') }}" method="POST" enctype="multipart/form-data" class="mb-6">
        @csrf
        <div class="flex flex-col gap-2 max-w-md">
            <label for="file" class="font-medium">Upload Excel File</label>
            <input type="file" name="file" id="file" required
                   class="border rounded p-2">
            <button type="submit" class="w-full dashboard__cta bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700 mt-2">
                Import
            </button>
        </div>
    </form>

    <div class="flex flex-wrap gap-2 mt-4">
        <a href="{{ route('questions.template') }}"
           class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition">
            Download Template
        </a>
        <a href="{{ route('questions.index') }}"
           class="bg-gray-100 text-gray-800 px-4 py-2 rounded hover:bg-gray-200 transition">
            Back to Questions
        </a>
    </div>
@endsection
