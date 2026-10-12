<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Post;
use App\Models\User;
use Illuminate\View\View;

/**
 * Public-facing tenant website — no auth required.
 * Pages are gated by the tenant's enabled modules via the `module:` middleware.
 */
class SiteController extends Controller
{
    public function home(): View
    {
        $hasBlog = tenant()->hasModule('posts');

        $featured = $hasBlog
            ? Post::published()->where('featured', true)->with('user', 'category')->latest()->first()
                ?? Post::published()->with('user', 'category')->latest()->first()
            : null;

        $latest = $hasBlog
            ? Post::published()->with('user', 'category')->latest()
                ->when($featured, fn ($q) => $q->where('id', '!=', $featured->id))
                ->take(6)->get()
            : collect();

        $popular = $hasBlog
            ? Post::published()->orderByDesc('views')->take(5)->get()
            : collect();

        $categories = $hasBlog
            ? Category::withCount(['posts' => fn ($q) => $q->published()])->orderBy('name')->take(8)->get()
            : collect();

        $employees = tenant()->hasModule('employees')
            ? Employee::latest()->take(4)->get()
            : collect();

        return view('app.site.home', compact('featured', 'latest', 'popular', 'categories', 'employees'));
    }

    public function blog(): View
    {
        $posts = Post::published()->with('user', 'category', 'tags')->latest()->paginate(9);
        $categories = Category::withCount(['posts' => fn ($q) => $q->published()])->orderBy('name')->get();
        $popular = Post::published()->orderByDesc('views')->take(5)->get();

        return view('app.site.blog', compact('posts', 'categories', 'popular'));
    }

    public function post(Post $post): View
    {
        abort_unless($post->status === 'published', 404);

        $post->increment('views');
        $post->load('user', 'category', 'tags');

        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->when($post->category_id, fn ($q) => $q->where('category_id', $post->category_id))
            ->latest()->take(3)->get();

        $comments = $post->comments()->where('approved', true)->oldest()->get();

        return view('app.site.post', compact('post', 'related', 'comments'));
    }

    public function categories(): View
    {
        $categories = Category::withCount(['posts' => fn ($q) => $q->published()])->orderBy('name')->get();

        return view('app.site.categories', compact('categories'));
    }

    public function category(Category $category): View
    {
        $posts = $category->posts()->published()->with('user', 'category')->latest()->paginate(9);
        $categories = Category::withCount(['posts' => fn ($q) => $q->published()])->orderBy('name')->get();

        return view('app.site.category', compact('category', 'posts', 'categories'));
    }

    public function author(User $user): View
    {
        $posts = $user->posts()->published()->with('category')->latest()->paginate(9);
        $stats = [
            'posts' => $posts->total(),
            'views' => $user->posts()->published()->sum('views'),
        ];

        return view('app.site.author', compact('user', 'posts', 'stats'));
    }

    public function team(): View
    {
        $employees = Employee::latest()->get();

        return view('app.site.team', compact('employees'));
    }
}
