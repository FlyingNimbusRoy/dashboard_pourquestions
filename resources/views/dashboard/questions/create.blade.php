@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Create New Question</h1>

    <form action="{{ route('questions.store') }}" method="POST"
          class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 space-y-6">
        @csrf
        <!-- Messages -->
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


        <!-- Question -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Question</label>
            <input type="text" name="vraag" value=""
                   class="w-full border rounded py-2 px-3">
        </div>

        <!-- Trivia -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Trivia</label>
            <input type="text" name="trivia" value=""
                   class="w-full border rounded py-2 px-3">
        </div>

        <!-- Difficulty -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Difficulty (1-5)</label>
            <input type="number" name="difficulty" value="3" min="1" max="5"
                   class="w-full border rounded py-2 px-3">
        </div>

        <!-- Category -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Category</label>
            <select name="category_id" class="w-full border rounded py-2 px-3">
                <option value="">-- Select --</option>
                @foreach(\App\Models\Category::all() as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Gamepack -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Gamepack</label>
            <select name="gamepack_id" class="w-full border rounded py-2 px-3">
                <option value="">-- Select --</option>
                @foreach(\App\Models\Gamepack::all() as $gamepack)
                    <option value="{{ $gamepack->id }}">{{ $gamepack->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- NSFW & Random -->
        <div class="flex gap-6">
            <label class="flex items-center gap-2">
                <input type="hidden" name="is_nsfw" value="0">
                <input type="checkbox" name="is_nsfw" value="1"> NSFW
            </label>
            <label class="flex items-center gap-2">
                <input type="hidden" name="is_random" value="0">
                <input type="checkbox" name="is_random" value="1"> Random
            </label>
        </div>

        <!-- Answers -->
        <div id="answers-wrapper" class="space-y-4">
            <h2 class="text-lg font-bold">Answers</h2>

            <div class="answer flex items-center gap-4">
                <input type="text" name="answers[0][answer]" placeholder="Answer text"
                       class="w-full border rounded py-2 px-3">
                <input type="hidden" name="answers[0][is_correct]" value="0">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="answers[0][is_correct]" value="1"> Correct
                </label>
            </div>

            <div class="answer flex items-center gap-4">
                <input type="text" name="answers[1][answer]" placeholder="Answer text"
                       class="w-full border rounded py-2 px-3">
                <input type="hidden" name="answers[1][is_correct]" value="0">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="answers[1][is_correct]" value="1"> Correct
                </label>
            </div>
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
        let answerIndex = 2; // start after 2 default answers
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
