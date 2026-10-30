<?php

declare(strict_types=1);

use App\Http\Controllers\App\CategoryController;
use App\Http\Controllers\App\ChatController;
use App\Http\Controllers\App\CommentController;
use App\Http\Controllers\App\DepartmentController;
use App\Http\Controllers\App\EmployeeController;
use App\Http\Controllers\App\JobRoleController;
use App\Http\Controllers\App\ManagerController;
use App\Http\Controllers\App\SiteSettingsController;
use App\Http\Controllers\App\TeamChatController;
use App\Http\Controllers\App\PostController;
use App\Http\Controllers\App\ProfileController;
use App\Http\Controllers\App\SiteController;
use App\Http\Controllers\App\ProjectController;
use App\Http\Controllers\App\TaskController;
use App\Http\Controllers\App\UserController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Routes here only run on tenant domains — tenancy is initialized from
| the domain, then all queries hit the tenant's own database.
|
| All route names are prefixed with "tenant." (e.g. tenant.login,
| tenant.dashboard) so they never collide with central route names.
|
| Modules are gated by Spatie permissions seeded per tenant
| (posts.*, employees.*, projects.*, tasks.*, users.manage).
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    \App\Http\Middleware\CheckTenantSuspended::class,
    \App\Http\Middleware\TenantViewOverrides::class,
])->name('tenant.')->group(function () {
    // Public website — no auth; pages are gated by the tenant's enabled modules
    Route::middleware('track.view')->group(function () {
        Route::get('/', [SiteController::class, 'home'])->name('home');

        Route::middleware('module:posts')->group(function () {
            Route::get('/blog', [SiteController::class, 'blog'])->name('blog');
            Route::get('/blog/{post}', [SiteController::class, 'post'])->name('blog.show');
            Route::post('/blog/{post}/comment', [CommentController::class, 'store'])->name('blog.comment');
            Route::get('/categories', [SiteController::class, 'categories'])->name('categories');
            Route::get('/category/{category}', [SiteController::class, 'category'])->name('category');
            Route::get('/author/{user}', [SiteController::class, 'author'])->name('author');
        });

        Route::get('/team', [SiteController::class, 'team'])->name('team')->middleware('module:employees');
    });

    // Tenant public storage — uploaded images stream from this tenant's own disk
    Route::get('/media/{path}', function (string $path) {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        return $disk->response($path);
    })->where('path', '.*')->name('media');

    // Public AI chatbot — rate limited, only when tenant has the chat module
    Route::post('/chat/handoff', [ChatController::class, 'handoff'])
        ->name('chat.handoff')->middleware('throttle:10,1');
    Route::get('/chat/feed', [ChatController::class, 'feed'])
        ->name('chat.feed')->middleware('throttle:30,1');
    Route::post('/chat', [ChatController::class, 'send'])
        ->name('chat.send')
        ->middleware(['module:chat', 'throttle:10,1']);
    Route::post('/chat/stream', [\App\Http\Controllers\App\ChatController::class, 'stream'])
        ->name('chat.stream')
        ->middleware(['module:chat', 'throttle:10,1']);

    // Public contact form → staff inbox
    Route::post('/contact', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'subject' => 'nullable|string|max:150',
            'message' => 'required|string|max:5000',
        ]);
        $contact = \App\Models\ContactMessage::create($data);
        \App\Models\Webhook::dispatch('contact.received', $contact->only('name', 'email', 'subject', 'message'));
        foreach (\App\Models\User::role('admin')->pluck('id') as $adminId) {
            \App\Models\UserNotification::notify($adminId, 'contact', 'New contact form submission',
                "{$contact->name}: {$contact->subject}", route('tenant.inbox'));
        }

        return back()->with('contact_sent', true);
    })->name('contact.send')->middleware('throttle:5,1');

    // Impersonation — super admin's one-time token logs in the first tenant admin
    Route::get('/impersonate/{token}', function (string $token) {
        abort_unless(tenant()->impersonation_token && hash_equals(tenant()->impersonation_token, $token), 403);
        tenant()->impersonation_token = null;
        tenant()->save();

        \Illuminate\Support\Facades\Auth::login(
            \App\Models\User::role('admin')->first() ?? abort(404)
        );

        return redirect()->route('tenant.dashboard')
            ->with('success', 'You are logged in via impersonation (support mode).');
    })->name('impersonate');

    // Two-factor challenge — user is logged out pending OTP, so these sit
    // OUTSIDE the auth group (session pending_2fa_user_id guards access).
    Route::get('/two-factor', [\App\Http\Controllers\App\TwoFactorController::class, 'challenge'])->name('2fa.challenge');
    Route::post('/two-factor', [\App\Http\Controllers\App\TwoFactorController::class, 'verify'])->name('2fa.verify');

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', function () {
            if (! tenant()->onboarded && auth()->user()->hasRole('admin')) {
                return redirect()->route('tenant.onboarding');
            }

            return view('app.dashboard', [
                'usersCount' => \App\Models\User::count(),
                'postsCount' => \App\Models\Post::count(),
                'viewsCount' => \App\Models\Post::sum('views'),
                'commentsCount' => \App\Models\Comment::where('approved', false)->count(),
                'employeesCount' => \App\Models\Employee::count(),
                'projectsCount' => \App\Models\Project::count(),
                'tasksCount' => \App\Models\Task::count(),
                'chatsCount' => \App\Models\ChatMessage::distinct('session_id')->count('session_id'),
                'recentPosts' => \App\Models\Post::with('user', 'category')->latest()->take(5)->get(),
                'recentComments' => \App\Models\Comment::with('post')->latest()->take(5)->get(),
                'viewsToday' => \App\Models\PageView::whereDate('created_at', today())->count(),
                'visitorsToday' => \App\Models\PageView::whereDate('created_at', today())->distinct('visitor_hash')->count('visitor_hash'),
                'openTickets' => \App\Models\ContactMessage::where('status', '!=', 'closed')->count(),
                'topPosts' => \App\Models\Post::where('status', 'published')->orderByDesc('views')->take(5)->get(),
                'topPages' => \App\Models\PageView::where('created_at', '>=', now()->subDays(14))
                    ->selectRaw('path, count(*) as total')->groupBy('path')
                    ->orderByDesc('total')->take(5)->get(),
                'chart' => [
                    'labels' => collect(range(13, 0))->map(fn ($d) => now()->subDays($d)->format('M j')),
                    'posts' => collect(range(13, 0))->map(fn ($d) => \App\Models\Post::whereDate('created_at', now()->subDays($d))->count()),
                    'messages' => collect(range(13, 0))->map(fn ($d) => \App\Models\Message::whereDate('created_at', now()->subDays($d))->count()),
                    'views' => collect(range(13, 0))->map(fn ($d) => \App\Models\PageView::whereDate('created_at', now()->subDays($d))->count()),
                    'visitors' => collect(range(13, 0))->map(fn ($d) => \App\Models\PageView::whereDate('created_at', now()->subDays($d))->distinct('visitor_hash')->count('visitor_hash')),
                ],
            ]);
        })->name('dashboard');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/tokens', function (\Illuminate\Http\Request $r) {
            $r->validate(['name' => 'required|string|max:100']);
            $token = $r->user()->createToken($r->name);

            return back()->with('token', $token->plainTextToken);
        })->name('tokens.store');
        Route::delete('/profile/tokens/{tokenId}', function (\Illuminate\Http\Request $r, $tokenId) {
            $r->user()->tokens()->where('id', $tokenId)->delete();

            return back();
        })->name('tokens.destroy');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Chat history — staff can browse + reply to visitor conversations
        Route::middleware(['module:chat', 'access:chat', 'permission:chats.view'])->group(function () {
            Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
            Route::post('/chats/reply', [ChatController::class, 'reply'])->name('chats.reply');
            Route::delete('/chats/{sessionId}', [ChatController::class, 'destroySession'])->name('chats.destroy')
                ->middleware('role:admin');
        });

        // Global search — across enabled modules
        Route::get('/search', function (\Illuminate\Http\Request $request) {
            $q = trim($request->query('q', ''));
            $results = collect();
            if (strlen($q) >= 2) {
                if (tenant()->hasModule('posts') && auth()->user()->hasAccessTo('posts')) {
                    $results = $results->merge(
                        \App\Models\Post::where('title', 'like', "%{$q}%")->take(5)->get()
                            ->map(fn ($p) => ['type' => 'Post', 'title' => $p->title, 'url' => route('tenant.posts.edit', $p)])
                    );
                }
                if (tenant()->hasModule('employees') && auth()->user()->hasAccessTo('employees')) {
                    $results = $results->merge(
                        \App\Models\Employee::where('first_name', 'like', "%{$q}%")->orWhere('last_name', 'like', "%{$q}%")->take(5)->get()
                            ->map(fn ($e) => ['type' => 'Employee', 'title' => $e->first_name . ' ' . $e->last_name, 'url' => route('tenant.employees.show', $e)])
                    );
                }
                if (tenant()->hasModule('projects') && auth()->user()->hasAccessTo('projects')) {
                    $results = $results->merge(
                        \App\Models\Project::where('name', 'like', "%{$q}%")->take(5)->get()
                            ->map(fn ($p) => ['type' => 'Project', 'title' => $p->name, 'url' => route('tenant.projects.edit', $p)])
                    );
                }
            }

            if ($request->wantsJson()) {
                return response()->json(['results' => $results]);
            }

            return view('app.search', ['q' => $q, 'results' => $results]);
        })->name('search');

        // Contact inbox — admin reads visitor enquiries as tickets
        Route::get('/inbox', function (\Illuminate\Http\Request $request) {
            $filter = in_array($request->status, \App\Models\ContactMessage::STATUSES)
                ? $request->status : null;

            $messages = \App\Models\ContactMessage::with('assignee:id,name')
                ->when($filter, fn ($q) => $q->where('status', $filter))
                ->latest()->paginate(20)->withQueryString();

            \App\Models\ContactMessage::whereNull('read_at')->update(['read_at' => now()]);

            return view('app.inbox', [
                'messages' => $messages,
                'filter' => $filter,
                'counts' => \App\Models\ContactMessage::statusCounts(),
                'agents' => \App\Models\User::orderBy('name')->get(['id', 'name']),
            ]);
        })->name('inbox')->middleware('role:admin');

        // Delete a ticket — admin only
        Route::delete('/inbox/{contactMessage}', function (\App\Models\ContactMessage $contactMessage) {
            $contactMessage->delete();

            return back()->with('success', __('Ticket deleted.'));
        })->name('inbox.destroy')->middleware('role:admin');

        // Update a ticket — status, priority, assignee
        Route::patch('/inbox/{contactMessage}', function (\Illuminate\Http\Request $request, \App\Models\ContactMessage $contactMessage) {
            $data = $request->validate([
                'status' => 'required|in:new,open,replied,closed',
                'priority' => 'required|in:low,medium,high',
                'assigned_to' => 'nullable|exists:users,id',
            ]);

            $data['resolved_at'] = $data['status'] === 'closed' && ! $contactMessage->resolved_at
                ? now() : $contactMessage->resolved_at;

            $contactMessage->update($data);

            return back()->with('success', __('Ticket updated.'));
        })->name('inbox.update')->middleware('role:admin');

        // AI-drafted reply for a contact enquiry
        Route::get('/inbox/{contactMessage}/suggest', function (\App\Models\ContactMessage $contactMessage) {
            $draft = app(\App\Services\AiChatService::class)->reply(
                "Draft a short, professional reply email to this customer enquiry from {$contactMessage->name} ".
                "(subject: {$contactMessage->subject}): \"{$contactMessage->message}\""
            );

            return response()->json(['draft' => $draft]);
        })->name('inbox.suggest')->middleware('role:admin');

        // Email verification — sends a signed link; doesn't block access
        Route::get('/verify-email', fn () => view('auth.verify-email'))->name('verification.notice');
        Route::get('/verify-email/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $r) {
            $r->fulfill();

            return redirect()->route('tenant.dashboard')->with('success', __('Email verified!'));
        })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
        Route::post('/email/verification-notification', function (\Illuminate\Http\Request $r) {
            if ($r->user()->hasVerifiedEmail()) {
                return back();
            }
            $r->user()->sendEmailVerificationNotification();

            return back()->with('status', 'verification-link-sent');
        })->middleware('throttle:6,1')->name('verification.send');

        Route::post('/profile/2fa/enable', [\App\Http\Controllers\App\TwoFactorController::class, 'enable'])->name('2fa.enable');
        Route::post('/profile/2fa/confirm', [\App\Http\Controllers\App\TwoFactorController::class, 'confirm'])->name('2fa.confirm');
        Route::delete('/profile/2fa', [\App\Http\Controllers\App\TwoFactorController::class, 'disable'])->name('2fa.disable');

        // Onboarding wizard — first-login brand setup for tenant admins
        Route::get('/onboarding', function () {
            if (tenant()->onboarded) {
                return redirect()->route('tenant.dashboard');
            }

            return view('app.onboarding');
        })->name('onboarding');
        Route::post('/onboarding', function (\Illuminate\Http\Request $r) {
            $data = $r->validate([
                'tagline' => 'nullable|string|max:255',
                'primary_color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
            ]);
            \App\Models\SiteSetting::current()->update(['tagline' => $data['tagline'] ?? null]);
            $t = tenant();
            $t->primary_color = $data['primary_color'] ?? null;
            $t->onboarded = true;
            $t->save();

            return redirect()->route('tenant.dashboard')->with('success', __('Welcome aboard! Your workspace is ready 🎉'));
        })->name('onboarding.store')->middleware('role:admin');

        // Notification center — unified feed for all events
        Route::get('/notifications', function () {
            return response()->json([
                'unread' => \App\Models\UserNotification::where('user_id', auth()->id())->whereNull('read_at')->count(),
                'items' => \App\Models\UserNotification::where('user_id', auth()->id())->latest()->take(15)->get(),
            ]);
        })->name('notifications');
        Route::post('/notifications/read-all', function () {
            \App\Models\UserNotification::where('user_id', auth()->id())->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->noContent();
        })->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', function (\App\Models\UserNotification $notification) {
            abort_unless($notification->user_id === auth()->id(), 403);
            $notification->update(['read_at' => now()]);

            return response()->noContent();
        })->name('notifications.read');
        Route::delete('/notifications/{notification}', function (\App\Models\UserNotification $notification) {
            abort_unless($notification->user_id === auth()->id(), 403);
            $notification->delete();

            return response()->noContent();
        })->name('notifications.destroy');
        Route::delete('/notifications', function () {
            \App\Models\UserNotification::where('user_id', auth()->id())->delete();

            return response()->noContent();
        })->name('notifications.clear');

        // Stripe checkout — activates automatically when STRIPE_SECRET is set
        Route::post('/billing/checkout/{plan}', function (string $plan) {
            $price = config("services.stripe.prices.{$plan}");
            abort_unless(config('services.stripe.secret') && $price, 404);

            $res = \Illuminate\Support\Facades\Http::asForm()->withToken(config('services.stripe.secret'))
                ->post('https://api.stripe.com/v1/checkout/sessions', [
                    'mode' => 'subscription',
                    'line_items' => [['price' => $price, 'quantity' => 1]],
                    'success_url' => route('tenant.billing', ['upgraded' => $plan]),
                    'cancel_url' => route('tenant.billing'),
                ]);
            abort_unless($res->successful(), 500, 'Stripe: ' . $res->body());

            return redirect($res->json('url'));
        })->name('billing.checkout')->middleware('role:admin');

        // Customization requests — tenant asks super admin for UI changes
        Route::middleware('role:admin')->group(function () {
            Route::get('/customizations', function () {
                $requests = \App\Models\CustomRequest::where('tenant_id', tenant()->id)
                    ->latest()->paginate(15);

                return view('app.customizations', ['requests' => $requests, 'customCss' => tenant('custom_css')]);
            })->name('customizations');
            Route::post('/customizations', function (\Illuminate\Http\Request $r) {
                $data = $r->validate([
                    'title' => 'required|string|max:150',
                    'details' => 'required|string|max:2000',
                    'module' => 'nullable|string|max:50',
                ]);
                \App\Models\CustomRequest::create($data + ['tenant_id' => tenant()->id]);

                // Ping the platform admin — request lands on their central inbox
                try {
                    $adminEmail = \App\Models\User::on('mysql')->orderBy('id')->value('email');
                    if ($adminEmail) {
                        \Illuminate\Support\Facades\Mail::raw(
                            "New customization request from " . tenant('name') . ":\n\n{$data['title']}\n\n{$data['details']}",
                            fn ($m) => $m->to($adminEmail)->subject('🎨 Tenant customization request')
                        );
                    }
                } catch (\Throwable) {}

                return back()->with('success', 'Request sent to the platform admin.');
            })->name('customizations.store');
            Route::post('/customizations/css', function (\Illuminate\Http\Request $r) {
                $data = $r->validate(['custom_css' => 'nullable|string|max:10000']);
                $t = tenant();
                $t->custom_css = $data['custom_css'];
                $t->save();

                return back()->with('success', 'Custom CSS saved.');
            })->name('customizations.css');
        });

        // Custom domain request — tenant admin submits, super admin approves
        Route::post('/billing/domain', function (\Illuminate\Http\Request $r) {
            $data = $r->validate(['domain' => 'required|string|max:100|regex:/^[a-z0-9.-]+$/']);
            $t = tenant();
            $t->pending_domain = $data['domain'];
            $t->save();

            return back()->with('success', "Domain request submitted — point {$data['domain']} to our servers (CNAME) and it will be activated.");
        })->name('billing.domain')->middleware('role:admin');

        // Plan & usage — tenant admin's billing view
        Route::get('/billing', function () {
            $tenant = tenant();
            $storagePath = storage_path('app/public');
            $bytes = 0;
            foreach (glob($storagePath . '/*', GLOB_ONLYDIR) ?: [] as $d) {
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($d, \FilesystemIterator::SKIP_DOTS)) as $f) {
                    $bytes += $f->getSize();
                }
            }

            return view('app.billing', [
                'plan' => $tenant->plan ?? 'custom',
                'planLabel' => \App\Models\Tenant::PLANS[$tenant->plan ?? 'custom']['label'] ?? 'Custom',
                'modules' => $tenant->modules ?? array_keys(\App\Models\Tenant::MODULES),
                'stats' => [
                    'Users' => \App\Models\User::count(),
                    'Messages' => \App\Models\Message::count(),
                    'Posts' => \App\Models\Post::count(),
                    'Employees' => \App\Models\Employee::count(),
                    'Storage' => number_format($bytes / 1048576, 1) . ' MB',
                ],
            ]);
        })->name('billing')->middleware('role:admin');

        // Leave requests — employee submits, admin approves
        Route::middleware('module:employees')->group(function () {
            Route::get('leaves', [\App\Http\Controllers\App\LeaveController::class, 'index'])->name('leaves.index');
            Route::post('leaves', [\App\Http\Controllers\App\LeaveController::class, 'store'])->name('leaves.store');
            Route::delete('leaves/{leave}', [\App\Http\Controllers\App\LeaveController::class, 'destroy'])->name('leaves.destroy');
            Route::post('leaves/{leave}/review', [\App\Http\Controllers\App\LeaveController::class, 'review'])->name('leaves.review')
                ->middleware('role:admin');
        });

        // Custom roles builder — permission matrix
        Route::middleware('role:admin')->group(function () {
            Route::get('roles', [\App\Http\Controllers\App\RoleController::class, 'index'])->name('roles.index');
            Route::post('roles', [\App\Http\Controllers\App\RoleController::class, 'store'])->name('roles.store');
            Route::patch('roles/{role}', [\App\Http\Controllers\App\RoleController::class, 'update'])->name('roles.update');
            Route::delete('roles/{role}', [\App\Http\Controllers\App\RoleController::class, 'destroy'])->name('roles.destroy');
        });

        // Webhooks — outbound event notifications
        Route::middleware('role:admin')->group(function () {
            Route::get('webhooks', [\App\Http\Controllers\App\WebhookController::class, 'index'])->name('webhooks.index');
            Route::post('webhooks', [\App\Http\Controllers\App\WebhookController::class, 'store'])->name('webhooks.store');
            Route::post('webhooks/{webhook}/toggle', [\App\Http\Controllers\App\WebhookController::class, 'toggle'])->name('webhooks.toggle');
            Route::delete('webhooks/{webhook}', [\App\Http\Controllers\App\WebhookController::class, 'destroy'])->name('webhooks.destroy');
        });

        // AI knowledge base — uploaded docs feed the visitor chatbot
        Route::middleware(['module:chat', 'access:chat', 'role:admin'])->group(function () {
            Route::get('knowledge', [\App\Http\Controllers\App\KnowledgeController::class, 'index'])->name('knowledge.index');
            Route::post('knowledge', [\App\Http\Controllers\App\KnowledgeController::class, 'store'])->name('knowledge.store');
            Route::delete('knowledge/{doc}', [\App\Http\Controllers\App\KnowledgeController::class, 'destroy'])->name('knowledge.destroy');
        });

        // Activity log — admin audit trail
        Route::get('/activity', function () {
            $logs = \App\Models\ActivityLog::with('user')->latest()->paginate(30);

            return view('app.activity', compact('logs'));
        })->name('activity')->middleware('role:admin');

        // Team Chat — WhatsApp-style staff messaging (module: messaging)
        Route::middleware(['module:messaging', 'access:messaging'])->group(function () {
            Route::get('messages', [TeamChatController::class, 'index'])->name('messages.index');
            Route::post('messages/start', [TeamChatController::class, 'start'])->name('messages.start');
            Route::post('messages/start-group', [TeamChatController::class, 'startGroup'])->name('messages.start-group');
            Route::get('messages/conversations', [TeamChatController::class, 'conversations'])->name('messages.conversations');
            Route::get('messages/{conversation}/feed', [TeamChatController::class, 'messages'])->name('messages.feed');
            Route::post('messages/{conversation}/send', [TeamChatController::class, 'send'])->name('messages.send')->middleware('throttle:20,1');
            Route::post('messages/{message}/delivered', [TeamChatController::class, 'delivered'])->name('messages.delivered');
            Route::patch('messages/{message}', [TeamChatController::class, 'update'])->name('messages.update');
            Route::delete('messages/{message}', [TeamChatController::class, 'destroy'])->name('messages.destroy');
            Route::post('messages/{message}/react', [TeamChatController::class, 'react'])->name('messages.react');
            Route::post('messages/{conversation}/preference', [TeamChatController::class, 'preference'])->name('messages.preference');
            Route::get('messages/{conversation}/search', [TeamChatController::class, 'search'])->name('messages.search');
            Route::get('messages/{conversation}/export', [TeamChatController::class, 'export'])->name('messages.export');
            Route::post('messages/{message}/task', [TeamChatController::class, 'makeTask'])->name('messages.task');
            Route::post('messages/{conversation}/read', [TeamChatController::class, 'read'])->name('messages.read');
        });

        // Users — admin only
        Route::resource('users', UserController::class)
            ->except('show')
            ->middleware('role:admin');

        // Site settings — admin only (branding shown on the public site)
        Route::middleware('role:admin')->group(function () {
            Route::get('settings', [SiteSettingsController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SiteSettingsController::class, 'update'])->name('settings.update');
            Route::post('settings/embed-key', function () {
                tenant()->embed_key = (string) \Illuminate\Support\Str::uuid();
                tenant()->save();

                return back()->with('success', 'Embed key regenerated — update the script tag on external sites.');
            })->name('settings.embed-key');
        });

        // Posts — writers create/edit own, admins can publish & manage all
        Route::middleware(['module:posts', 'access:posts'])->group(function () {
            Route::get('posts', [PostController::class, 'index'])->name('posts.index')->middleware('permission:posts.view');
        Route::get('posts/create', [PostController::class, 'create'])->name('posts.create')->middleware('permission:posts.create');
        Route::post('posts', [PostController::class, 'store'])->name('posts.store')->middleware('permission:posts.create');
        Route::get('posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit')->middleware('permission:posts.edit');
        Route::put('posts/{post}', [PostController::class, 'update'])->name('posts.update')->middleware('permission:posts.edit');
        Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy')->middleware('permission:posts.delete');

        // Categories & comments — blog management (posts/ prefix: plain /categories
        // belongs to the public site; same-method same-URI routes would overwrite it)
        Route::get('posts/categories', [CategoryController::class, 'index'])->name('categories.index')->middleware('permission:posts.view');
        Route::post('posts/categories', [CategoryController::class, 'store'])->name('categories.store')->middleware('permission:posts.create');
        Route::put('posts/categories/{category}', [CategoryController::class, 'update'])->name('categories.update')->middleware('permission:posts.edit');
        Route::delete('posts/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy')->middleware('permission:posts.delete');
        Route::get('comments', [CommentController::class, 'index'])->name('comments.index')->middleware('permission:posts.view');
        Route::patch('comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve')->middleware('permission:posts.edit');
        Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy')->middleware('permission:posts.delete');
        });

        // Employees — hr + admin (EMS: departments, job roles, managers, project assignment)
        Route::middleware(['module:employees', 'access:employees'])->group(function () {
            Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index')->middleware('permission:employees.view');
        Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create')->middleware('permission:employees.create');
        Route::get('employees/template', [EmployeeController::class, 'template'])->name('employees.template')->middleware('permission:employees.view');
        Route::get('employees/import', fn () => view('app.employees.import'))->name('employees.import.form')->middleware('permission:employees.create');
        Route::post('employees/import', [EmployeeController::class, 'import'])->name('employees.import')->middleware('permission:employees.create');
        Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store')->middleware('permission:employees.create');
        Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show')->middleware('permission:employees.view');
        Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit')->middleware('permission:employees.edit');
        Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update')->middleware('permission:employees.edit');
        Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy')->middleware('permission:employees.delete');

        // Departments
        Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index')->middleware('permission:employees.view');
        Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store')->middleware('permission:employees.create');
        Route::put('departments/{department}', [DepartmentController::class, 'update'])->name('departments.update')->middleware('permission:employees.edit');
        Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy')->middleware('permission:employees.delete');

        // Job Roles
        Route::get('job-roles', [JobRoleController::class, 'index'])->name('job-roles.index')->middleware('permission:employees.view');
        Route::post('job-roles', [JobRoleController::class, 'store'])->name('job-roles.store')->middleware('permission:employees.create');
        Route::put('job-roles/{jobRole}', [JobRoleController::class, 'update'])->name('job-roles.update')->middleware('permission:employees.edit');
        Route::delete('job-roles/{jobRole}', [JobRoleController::class, 'destroy'])->name('job-roles.destroy')->middleware('permission:employees.delete');

        // Managers
        Route::get('managers', [ManagerController::class, 'index'])->name('managers.index')->middleware('permission:employees.view');
        Route::post('managers', [ManagerController::class, 'store'])->name('managers.store')->middleware('permission:employees.create');
        Route::put('managers/{manager}', [ManagerController::class, 'update'])->name('managers.update')->middleware('permission:employees.edit');
        Route::delete('managers/{manager}', [ManagerController::class, 'destroy'])->name('managers.destroy')->middleware('permission:employees.delete');
        });

        // Projects — admin manages, members view
        Route::middleware(['module:projects', 'access:projects'])->group(function () {
            Route::get('projects', [ProjectController::class, 'index'])->name('projects.index')->middleware('permission:projects.view');
        Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create')->middleware('permission:projects.manage');
        Route::post('projects', [ProjectController::class, 'store'])->name('projects.store')->middleware('permission:projects.manage');
        Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show')->middleware('permission:projects.view');
        Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit')->middleware('permission:projects.manage');
        Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update')->middleware('permission:projects.manage');
        Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy')->middleware('permission:projects.manage');

        // Tasks — view all, manage restricted, status updates for assignees
        Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index')->middleware('permission:tasks.view');
        Route::get('tasks/create', [TaskController::class, 'create'])->name('tasks.create')->middleware('permission:tasks.create');
        Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store')->middleware('permission:tasks.create');
        Route::get('tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit')->middleware('permission:tasks.edit');
        Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update')->middleware('permission:tasks.edit');
        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status')->middleware('permission:tasks.update_status');
        Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy')->middleware('permission:tasks.delete');
        });
    });

    require __DIR__.'/tenant-auth.php';
});
