@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Recategorisation Tool</h1>

    <!-- Success/Error messages -->
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

    <!-- Category selection -->
    <form action="{{ route('dashboard.tools.recategorisation') }}" method="GET"
          class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 md:gap-6 mb-6">
        <select name="category" class="w-full border rounded px-3 py-2">
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        <button type="submit"
                class="w-full px-5 py-3 dashboard__cta text-white rounded-lg shadow hover:bg-blue-700 transition text-center">
            Load Questions
        </button>
    </form>

    <!-- Questions Table -->
    @if(isset($questions) && $questions->count())
        <table class="min-w-full bg-white border rounded">
            <thead>
            <tr>
                <th class="py-2 px-4 border-b">Question</th>
                <th class="py-2 px-4 border-b">Reassign</th>
            </tr>
            </thead>
            <tbody id="question-table">
            @foreach($questions as $q)
                <tr id="question-{{ $q->id }}" class="border-b">
                    <td class="py-2 px-4">{{ $q->vraag }}</td>
                    <td class="py-2 px-4">
                        <form action="{{ route('dashboard.tools.recategorisation.update', $q->id) }}"
                              method="POST"
                              class="flex gap-2 reassign-form"
                              data-question-id="{{ $q->id }}">
                            @csrf
                            @method('PATCH')
                            <select name="category_id" class="border rounded px-2 py-1">
                                @foreach($categories as $cat)
                                    @if($cat->id !== $q->category_id)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700 text-sm">
                                Move
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @elseif(request('category'))
        <p class="text-gray-600">No questions found in this category.</p>
    @endif

    <script>
        // Intercept reassign form submit to remove row instantly
        document.querySelectorAll('.reassign-form').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                let questionId = form.dataset.questionId;

                let response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (response.ok) {
                    document.getElementById('question-' + questionId).remove();
                } else {
                    alert('Error moving question.');
                }
            });
        });
    </script>
@endsection
