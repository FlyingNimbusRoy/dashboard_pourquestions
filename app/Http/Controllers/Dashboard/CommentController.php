<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comment::latest()->paginate(40);
        return view('dashboard.comments.index', compact('comments'));
    }

    public function create()
    {
        return view('dashboard.comments.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:255',
        ]);

        Comment::create($validated);

        return redirect()->route('dashboard.comments.index')->with('success', 'Comment added!');
    }

    public function edit(Comment $comment)
    {
        return view('dashboard.comments.form', compact('comment'));
    }

    public function update(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:255',
        ]);

        $comment->update($validated);

        return redirect()->route('dashboard.comments.index')->with('success', 'Comment updated!');
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        return redirect()->route('dashboard.comments.index')->with('success', 'Comment deleted!');
    }
}
