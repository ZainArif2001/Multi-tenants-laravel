<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::with('user')->latest()->get();

        return view('app.posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('app.posts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
        ]);

        $request->user()->posts()->create([
            ...$validated,
            // Only users with posts.publish can publish directly; others create drafts.
            'status' => $request->user()->can('posts.publish') && $request->boolean('publish')
                ? 'published' : 'draft',
        ]);

        return redirect()->route('tenant.posts.index')->with('success', 'Post created successfully.');
    }

    public function edit(Request $request, Post $post): View
    {
        $this->authorizePost($request, $post);

        return view('app.posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->authorizePost($request, $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
        ]);

        $post->update([
            ...$validated,
            'status' => $request->user()->can('posts.publish') && $request->boolean('publish')
                ? 'published' : 'draft',
        ]);

        return redirect()->route('tenant.posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $this->authorizePost($request, $post);

        $post->delete();

        return redirect()->route('tenant.posts.index')->with('success', 'Post deleted successfully.');
    }

    /**
     * Writers can only touch their own posts; posts.publish holders (admin) can touch any.
     */
    protected function authorizePost(Request $request, Post $post): void
    {
        $owns = $post->user_id === $request->user()->id;

        abort_unless($owns || $request->user()->can('posts.publish'), 403);
    }
}
