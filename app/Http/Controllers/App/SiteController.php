<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Post;
use Illuminate\View\View;

/**
 * Public-facing tenant website — no auth required.
 * Pages are gated by the tenant's enabled modules via the `module:` middleware.
 */
class SiteController extends Controller
{
    public function home(): View
    {
        $posts = tenant()->hasModule('posts')
            ? Post::where('status', 'published')->latest()->take(3)->get()
            : collect();

        $employees = tenant()->hasModule('employees')
            ? Employee::latest()->take(4)->get()
            : collect();

        return view('app.site.home', compact('posts', 'employees'));
    }

    public function blog(): View
    {
        $posts = Post::where('status', 'published')->latest()->get();

        return view('app.site.blog', compact('posts'));
    }

    public function post(Post $post): View
    {
        abort_unless($post->status === 'published', 404);

        return view('app.site.post', compact('post'));
    }

    public function team(): View
    {
        $employees = Employee::latest()->get();

        return view('app.site.team', compact('employees'));
    }
}
