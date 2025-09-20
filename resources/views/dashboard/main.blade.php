@extends('dashboard')

@section('content')
    <div class="flex gap-6">
        <!-- Left column: Notifications -->
        <div class="w-1/3 flex flex-col space-y-4">

            @if(($gradingTotal ?? 0) > 0 || ($validationTotal ?? 0) > 0 || ($quotaRemaining ?? 0) > 0)
                @if($gradingTotal ?? 0 > 0)
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 px-4 py-3 rounded">
                        ⚡ You have <strong>{{ $gradingTotal }}</strong> questions to grade for difficulty.
                        <a href="{{ route('dashboard.tools.grading') }}" class="underline ml-2">Open Grading Tool</a>
                    </div>
                @endif

                @if($validationTotal ?? 0 > 0)
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded">
                        ⚡ You have <strong>{{ $validationTotal }}</strong> questions to validate.
                        <a href="{{ route('dashboard.tools.relevancy') }}" class="underline ml-2">Open Relevancy
                            Tool</a>
                    </div>
                @endif

                @if($quotaRemaining > 0)
                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 px-4 py-3 rounded">
                        📝 We should still create <strong>{{ $quotaRemaining }}</strong> questions this month to hit our
                        quota! (quota: {{ $monthlyQuota }}).
                        <a href="{{ route('questions.create') }}" class="underline ml-2">Add Questions</a>
                    </div>
                @endif

                @if($similarityTotal > 0)
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 px-4 py-3 rounded">
                        🔍 You have <strong>{{ $similarityTotal }}</strong> unhandled question similarities.
                        <a href="{{ route('dashboard.tools.similarities') }}" class="underline ml-2">Open Similarity
                            Tool</a>
                    </div>
                @endif

            @else
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded">
                    ✅ Everything is up to date. Nothing to worry about!
                </div>
            @endif
        </div>

        <!-- Right column: Welcome message / other content -->
        <div class="w-2/3 bg-white p-6 rounded shadow">
            <h2 class="text-xl font-semibold mb-4">Welcome to the Dashboard</h2>
            <p class="text-gray-600">
                Select a tool or section from the sidebar to get started.
            </p>
            <!-- Additional content can go here -->
        </div>
    </div>
@endsection
