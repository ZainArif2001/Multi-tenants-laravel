<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::with('user', 'category')->withCount('comments')->latest()->paginate(15);

        return view('app.posts.index', compact('posts'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->pluck('name', 'id');

        return view('app.posts.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $post = $request->user()->posts()->create($this->validated($request));
        $this->syncTags($post, $request->input('tags'));

        return redirect()->route('tenant.posts.index')->with('success', 'Post created successfully.');
    }

    public function edit(Request $request, Post $post): View
    {
        $this->authorizePost($request, $post);

        $categories = Category::orderBy('name')->pluck('name', 'id');
        $post->load('tags');

        return view('app.posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->authorizePost($request, $post);

        $oldImage = $post->featured_image;
        $validated = $this->validated($request, $post);
        $post->update($validated);
        $this->syncTags($post, $request->input('tags'));

        // A new upload replaced the old tenant-storage image — clean it up
        if (isset($validated['featured_image']) && $oldImage && ! str_starts_with($oldImage, 'http')) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('tenant.posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $this->authorizePost($request, $post);

        if ($post->featured_image && ! str_starts_with($post->featured_image, 'http')) {
            Storage::disk('public')->delete($post->featured_image);
        }
        $post->delete();

        return redirect()->route('tenant.posts.index')->with('success', 'Post deleted successfully.');
    }

    protected function validated(Request $request, ?Post $post = null): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'body' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        // Upload into this tenant's own public disk (storage/tenant{id}/app/public/posts/)
        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        } else {
            unset($validated['featured_image']);
        }

        $validated['slug'] = Post::uniqueSlug(
            ($validated['slug'] ?? null) ?: $validated['title'],
            $post?->id
        );

        // Only posts.publish holders can publish or feature a post
        if ($request->user()->can('posts.publish')) {
            $validated['status'] = $request->boolean('publish') ? 'published' : 'draft';
            $validated['featured'] = $request->boolean('featured');
        }

        return $validated;
    }

    /**
     * Accepts a comma-separated tag string ("laravel, php, tips") and syncs it.
     */
    protected function syncTags(Post $post, ?string $tags): void
    {
        $ids = collect(explode(',', (string) $tags))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->map(fn ($name) => Tag::firstOrCreate(['name' => $name])->id);

        $post->tags()->sync($ids);
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
