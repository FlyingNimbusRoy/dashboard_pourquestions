<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

        $questions = $query->latest()->paginate(40);

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
        try {
            $validated = $request->validate([
                'vraag' => 'required|string|max:255',
                'trivia' => 'nullable|string|max:255',
                'difficulty' => 'required|integer|min:1|max:5',
                'is_random' => 'required|boolean',
                'is_nsfw' => 'required|boolean',
                'category_id' => 'nullable|exists:categories,id',
                'gamepack_id' => 'nullable|exists:gamepacks,id',
                'answers.*.answer' => 'required|string|max:255',
                'answers.*.is_correct' => 'boolean',
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please fix the errors below and try again.');
        }

        $validated['maker_id'] = auth()->id(); // auto-assign maker

        $question = Question::create($validated);

        if ($request->filled('answers')) {
            foreach ($request->answers as $answerData) {
                $question->answers()->create([
                    'answer' => $answerData['answer'],
                    'is_correct' => $answerData['is_correct'] ?? 0,
                ]);
            }
        }

        return redirect()->route('questions.index')->with('success', 'Question created!');
    }


    public function edit(Question $question)
    {
        return view('dashboard.questions.edit', compact('question'));
    }

    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'vraag' => 'required|string|max:255',
            'trivia' => 'nullable|string|max:255',
            'difficulty' => 'required|integer|min:1|max:5',
            'is_random' => 'boolean',
            'is_nsfw' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'gamepack_id' => 'nullable|exists:gamepacks,id',
            'answers.*.answer' => 'required|string|max:255',
            'answers.*.is_correct' => 'boolean',
        ]);

        $question->update($validated);

        // Replace old answers
        $question->answers()->delete();
        if ($request->filled('answers')) {
            foreach ($request->answers as $answerData) {
                $question->answers()->create([
                    'answer' => $answerData['answer'],
                    'is_correct' => $answerData['is_correct'] ?? 0,
                ]);
            }
        }

        return redirect()->route('questions.index')->with('success', 'Question updated!');
    }

    public function destroy(Question $question)
    {
        // delete answers first
        $question->answers()->delete();

        // then delete question
        $question->delete();

        return redirect()->route('questions.index')->with('success', 'Question deleted!');
    }
}
