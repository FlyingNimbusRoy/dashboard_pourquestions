@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Question difficulty grading Tool</h1>

    @if($total === 0)
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            🎉 Nothing to grade for Difficulty! All questions are up-to-date.
        </div>
    @else
        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 rounded h-4 mb-6">
            <div id="progress-bar"
                 class="bg-green-500 h-4 rounded"
                 data-completed="0"
                 data-total="{{ $total }}"
                 style="width: 0%">
            </div>
        </div>

        <p class="mb-4 text-gray-600">You have <strong>{{ $total }}</strong> questions to grade for difficulty.</p>

        <div class="space-y-4">
            @foreach($questions as $question)
                <div id="question-{{ $question->id }}" class="p-4 border rounded shadow bg-white relative">
                    <!-- Current difficulty badge -->
                    <span class="absolute top-2 position-right px-2 py-1 rounded text-white text-sm font-semibold
                        @php
                            $badgeColors = [
                                1 => 'dark_blue text-white',
                                2 => 'light_blue text-white',
                                3 => 'default_grey text-white',
                                4 => 'light_red text-white',
                                5 => 'dark_red text-white',
                            ];
                        @endphp
                        {{ $badgeColors[$question->difficulty] ?? 'bg-gray-400' }}
                    ">
                        {{ $question->difficulty }}
                    </span>

                    <p class="font-semibold">{{ $question->vraag }}</p>
                    @if($question->trivia)
                        <p class="text-sm text-gray-500 mb-2">{{ $question->trivia }}</p>
                    @endif

                    @if($question->answers->isNotEmpty())
                        <ul class="text-sm text-gray-700 mb-2 list-disc list-inside">
                            @foreach($question->answers as $answer)
                                <li>
                                    {{ $answer->answer }}
                                    @if($answer->is_correct)
                                        <span class="text-green-600 font-semibold">(Correct)</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="flex gap-2">
                        @php
                            $colors = [
                                1 => 'dark_blue text-white',
                                2 => 'light_blue text-white',
                                3 => 'default_grey text-white',
                                4 => 'light_red text-white',
                                5 => 'dark_red text-white',
                            ];
                        @endphp

                        @foreach($colors as $value => $class)
                            <button
                                class="px-3 py-1 rounded {{ $class }} hover:opacity-80 transition"
                                onclick="gradeQuestion({{ $question->id }}, {{ $value }})">
                                {{ $value }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <script>
        function gradeQuestion(id, difficulty) {
            fetch(`/dashboard/tools/grading/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({difficulty})
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Remove question card
                        const qEl = document.getElementById(`question-${id}`);
                        qEl.remove();

                        // Update progress bar
                        const bar = document.getElementById('progress-bar');
                        const completed = parseInt(bar.dataset.completed) + 1;
                        const total = parseInt(bar.dataset.total);
                        bar.dataset.completed = completed;
                        bar.style.width = `${(completed / total) * 100}%`;
                    }
                });
        }
    </script>
@endsection
