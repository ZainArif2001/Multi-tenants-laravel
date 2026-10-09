<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the tenant login view.
     */
    public function create(): View
    {
        return view('app.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended($this->landingUrl($request->user()));
    }

    /**
     * Send the user to their first allowed module — e.g. a user who
     * only has blog access lands on /posts, not the dashboard.
     */
    protected function landingUrl($user): string
    {
        return match (true) {
            $user->hasAccessTo('posts') => route('tenant.posts.index', absolute: false),
            $user->hasAccessTo('employees') => route('tenant.employees.index', absolute: false),
            $user->hasAccessTo('projects') => route('tenant.projects.index', absolute: false),
            $user->hasAccessTo('chat') => route('tenant.chats.index', absolute: false),
            default => route('tenant.dashboard', absolute: false),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('tenant.login');
    }
}
