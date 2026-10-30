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

        // 2FA enabled → hold login, ask for OTP
        if ($request->user()->google2fa_secret) {
            $userId = $request->user()->id;
            Auth::logout();
            $request->session()->regenerate();
            $request->session()->put('pending_2fa_user_id', $userId);

            return redirect()->route('tenant.2fa.challenge');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->landingUrl($request->user()));
    }

    /**
     * Send the user to their first allowed module — e.g. a user who
     * only has blog access lands on /posts, not the dashboard.
     */
    protected function landingUrl($user): string
    {
        // Single module access → land straight on it.
        // Multiple modules → dashboard so they can pick.
        $access = collect(['posts', 'employees', 'projects', 'chat', 'messaging'])
            ->filter(fn ($m) => $user->hasAccessTo($m))
            ->values();

        if ($access->count() !== 1) {
            return route('tenant.dashboard', absolute: false);
        }

        return match ($access->first()) {
            'posts' => route('tenant.posts.index', absolute: false),
            'employees' => route('tenant.employees.index', absolute: false),
            'projects' => route('tenant.projects.index', absolute: false),
            'chat' => route('tenant.chats.index', absolute: false),
            'messaging' => route('tenant.messages.index', absolute: false),
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
