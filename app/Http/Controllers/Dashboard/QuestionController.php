<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = Question::query();

        // Search by question text
        if ($request->filled('search')) {
            $query->where('vraag', 'like', "%{$request->search}%");
        }

        // Filters
        if ($request->filled('is_nsfw')) {
            $query->where('is_nsfw', $request->is_nsfw);
        }

        if ($request->filled('is_random')) {
            $query->where('is_random', $request->is_random);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('gamepack_id')) {
            $query->where('gamepack_id', $request->gamepack_id);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        $questions = $query->latest()->paginate(20);

        // Preserve query parameters in pagination links
        $questions->appends($request->only('search', 'is_nsfw', 'is_random', 'category_id', 'gamepack_id', 'difficulty'));

        return view('dashboard.questions.index', compact('questions'));
    }



    public function create()
    {
        return view('dashboard.questions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vraag' => 'required|string|max:255',
            'difficulty' => 'required|integer',
        ]);

        Question::create($validated);

        return redirect()->route('questions.index');
    }

    public function edit(Question $question)
    {
        return view('dashboard.questions.edit', compact('question'));
    }

    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'vraag' => 'required|string|max:255',
            'difficulty' => 'required|integer',
        ]);

        $question->update($validated);

        return redirect()->route('questions.index');
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return redirect()->route('questions.index');
    }
}
