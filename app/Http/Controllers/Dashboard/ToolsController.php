<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ToolsController extends Controller
{
    public function index()
    {
        return view('dashboard.tools.index');
    }

    public function grading()
    {
        $questions = Question::with('answers') // eager-load answers
        ->where('updated_at', '<', Carbon::now()->subMonth())
            ->orderBy('updated_at', 'asc')
            ->limit(50)
            ->get();

        $total = $questions->count();

        return view('dashboard.tools.grading', compact('questions', 'total'));
    }


    public function updateGrading(Request $request, Question $question)
    {
        $request->validate([
            'difficulty' => 'required|integer|min:1|max:5',
        ]);

        $question->difficulty = $request->difficulty;
        $question->touch();
        $question->save();

        return response()->json(['success' => true]);
    }


    public function relevancyChecker()
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6);

        // Fetch 50 oldest questions last validated more than 6 months ago
        $questions = Question::with('answers')
            ->where('last_validated', '<', $sixMonthsAgo)
            ->orderBy('last_validated', 'asc')
            ->limit(50)
            ->get();

        $total = $questions->count();

        return view('dashboard.tools.relevancy', compact('questions', 'total'));
    }

    public function validateQuestion(Request $request)
    {
        $question = Question::findOrFail($request->id);
        $question->last_validated = Carbon::now();
        $question->save();

        return response()->json(['success' => true, 'last_validated' => $question->last_validated->toDateTimeString()]);
    }
}
