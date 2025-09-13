@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">
        {{ isset($question) ? 'Edit Question' : 'Create New Question' }}
    </h1>

    <form action="{{ isset($question) ? route('questions.update', $question) : route('questions.store') }}"
          method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-6">
        @csrf
        @if(isset($question))
            @method('PUT')
        @endif

        <!-- Question -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Question</label>
            <input type="text" name="vraag" value="{{ old('vraag', $question->vraag ?? '') }}"
                   class="w-full border rounded py-2 px-3">
        </div>

        <!-- Trivia -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Trivia</label>
            <input type="text" name="trivia" value="{{ old('trivia', $question->trivia ?? '') }}"
                   class="w-full border rounded py-2 px-3">
        </div>

        <!-- Difficulty -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Difficulty (1-5)</label>
            <input type="number" name="difficulty" value="{{ old('difficulty', $question->difficulty ?? 3) }}"
                   min="1" max="5" class="w-full border rounded py-2 px-3">
        </div>

        <!-- Category -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Category</label>
            <select name="category_id" class="w-full border rounded py-2 px-3">
                <option value="">-- Select --</option>
                @foreach(\App\Models\Category::all() as $category)
                    <option value="{{ $category->id }}"
                        {{ old('category_id', $question->category_id ?? '') == $category->id ? 'selected':'' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Gamepack -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Gamepack</label>
            <select name="gamepack_id" class="w-full border rounded py-2 px-3">
                <option value="">-- Select --</option>
                @foreach(\App\Models\Gamepack::all() as $gamepack)
                    <option value="{{ $gamepack->id }}"
                        {{ old('gamepack_id', $question->gamepack_id ?? '') == $gamepack->id ? 'selected':'' }}>
                        {{ $gamepack->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- NSFW & Random -->
        <div class="flex gap-6">
            <label class="flex items-center gap-2">
                <input type="hidden" name="is_nsfw" value="0">
                <input type="checkbox" name="is_nsfw" value="1"
                    {{ old('is_nsfw', $question->is_nsfw ?? false) ? 'checked' : '' }}>
                NSFW
            </label>
            <label class="flex items-center gap-2">
                <input type="hidden" name="is_random" value="0">
                <input type="checkbox" name="is_random" value="1"
                    {{ old('is_random', $question->is_random ?? false) ? 'checked' : '' }}>
                Random
            </label>
        </div>

        <!-- Answers -->
        <div id="answers-wrapper" class="space-y-4">
            <h2 class="text-lg font-bold">Answers</h2>

            @php
                $answers = old('answers', isset($question) ? $question->answers->toArray() : [[], []]);
            @endphp

            @foreach($answers as $i => $answer)
                <div class="answer flex items-center gap-4">
                    <input type="text" name="answers[{{ $i }}][answer]"
                           value="{{ $answer['answer'] ?? '' }}"
                           placeholder="Answer text"
                           class="w-full border rounded py-2 px-3">

                    <input type="hidden" name="answers[{{ $i }}][is_correct]" value="0">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="answers[{{ $i }}][is_correct]" value="1"
                            {{ !empty($answer['is_correct']) ? 'checked' : '' }}>
                        Correct
                    </label>
                </div>
            @endforeach
        </div>

        <button type="button" id="add-answer-btn"
                class="bg-gray-200 px-4 py-2 rounded shadow hover:bg-gray-300">
            + Add Answer
        </button>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Save
            </button>
            <a href="{{ route('questions.index') }}" class="text-blue-500 hover:underline">Cancel</a>
        </div>
    </form>

    <script>
        let answerIndex = {{ count($answers) }};
        const maxAnswers = 4;

        document.getElementById('add-answer-btn').addEventListener('click', () => {
            if (answerIndex >= maxAnswers) {
                alert("You can only add up to 4 answers.");
                return;
            }

            const wrapper = document.getElementById('answers-wrapper');
            const div = document.createElement('div');
            div.classList.add('answer', 'flex', 'items-center', 'gap-4');
            div.innerHTML = `
                <input type="text" name="answers[${answerIndex}][answer]" placeholder="Answer text"
                       class="w-full border rounded py-2 px-3">

                <input type="hidden" name="answers[${answerIndex}][is_correct]" value="0">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="answers[${answerIndex}][is_correct]" value="1"> Correct
                </label>
            `;
            wrapper.appendChild(div);
            answerIndex++;
        });
    </script>
@endsection
