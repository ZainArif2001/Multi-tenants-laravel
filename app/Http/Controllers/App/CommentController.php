<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    /**
     * Public — visitor leaves a comment on a published post (queued for approval).
     */
    public function store(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->status === 'published', 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'body' => 'required|string|max:2000',
        ]);

        $post->comments()->create($validated);

        return back()->with('success', 'Comment submitted — it will appear after approval.');
    }

    /**
     * Staff — moderation queue.
     */
    public function index(): View
    {
        $comments = Comment::with('post')->latest()->paginate(20);

        return view('app.comments.index', compact('comments'));
    }

    public function approve(Comment $comment): RedirectResponse
    {
        $comment->update(['approved' => true]);

        return back()->with('success', 'Comment approved.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
