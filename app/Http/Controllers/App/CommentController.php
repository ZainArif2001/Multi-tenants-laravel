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

        // AI moderation — Gemini flags spam/toxic comments (skipped if no key)
        if (config('services.gemini.api_key')) {
            $verdict = rescue(fn () => \Illuminate\Support\Facades\Http::timeout(8)->post(
                sprintf('https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
                    config('services.gemini.model', 'gemini-2.0-flash'), config('services.gemini.api_key')),
                ['contents' => [['role' => 'user', 'parts' => [['text' =>
                    'Reply with exactly one word — SAFE or SPAM — for this blog comment: ' . $validated['body']
                ]]]]]
            )->json('candidates.0.content.parts.0.text'), 'SAFE');

            if (str_contains(strtoupper((string) $verdict), 'SPAM')) {
                return back()->with('success', 'Comment submitted — it will appear after approval.');
            }
        }

        $post->comments()->create($validated);

        foreach (\App\Models\User::role('admin')->pluck('id') as $adminId) {
            \App\Models\UserNotification::notify($adminId, 'comment', 'New comment awaiting approval',
                \Illuminate\Support\Str::limit($validated['body'], 60), route('tenant.comments.index'));
        }

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
