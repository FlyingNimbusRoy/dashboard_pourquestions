@extends('dashboard')

@section('content')
    <div class="container mx-auto py-6">
        <h1 class="text-2xl font-bold mb-4">Relevancy Checker Tool</h1>

        @if($total === 0)
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                🎉 Nothing to validate! All questions are up-to-date.
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

            <p class="mb-4 text-gray-600">You have <strong>{{ $total }}</strong> questions to validate.</p>

            <table class="min-w-full border border-gray-200 divide-y divide-gray-200">
                <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left">Question</th>
                    <th class="px-4 py-2 text-left">Answers</th>
                    <th class="px-4 py-2 text-left">Last Validated</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @foreach($questions as $question)
                    <tr id="question-{{ $question->id }}">
                        <td class="px-4 py-2">{{ $question->vraag }}</td>
                        <td class="px-4 py-2">
                            <ul class="list-disc list-inside text-sm">
                                @foreach($question->answers as $answer)
                                    <li>
                                        {{ $answer->answer }}
                                        @if($answer->is_correct)
                                            <span class="text-green-600 font-semibold">(Correct)</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="px-4 py-2" id="last-validated-{{ $question->id }}">
                            {{ $question->last_validated }}
                        </td>
                        <td class="px-4 py-2 flex gap-2">
                            <button
                                onclick="validateQuestion({{ $question->id }})"
                                class="px-3 py-1 rounded bg-green-500 text-white hover:bg-green-500 transition">
                                Validate
                            </button>
                            <a href="{{ route('questions.edit', $question->id) }}" target="_blank"
                               class="px-3 py-1 rounded bg-blue-500 text-white hover:bg-blue-500 transition">
                                Edit
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <script>
        function validateQuestion(questionId) {
            fetch("{{ route('dashboard.tools.relevancy.validate') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: questionId })
            })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Update timestamp
                        document.getElementById('last-validated-' + questionId).innerText = data.last_validated;

                        // Remove row from table
                        const row = document.getElementById('question-' + questionId);
                        row.remove();

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
