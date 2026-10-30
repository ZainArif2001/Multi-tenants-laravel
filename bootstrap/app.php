<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            // Central routes only run on central domains.
            // On tenant domains they don't match, so /login etc. → 404.
            // dd(config('tenancy.central_domains'));
            foreach (config('tenancy.central_domains') as $domain) {
                Route::middleware('web')
                    ->domain($domain)
                    ->group(base_path('routes/web.php'));
            }

            // Tenant API — token-authenticated, tenant-isolated via domain.
            Route::middleware([
                'api',
                \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
                \App\Http\Middleware\CheckTenantSuspended::class,
            ])
                ->prefix('api')
                ->group(base_path('routes/api-tenant.php'));

            // Public embed API — external sites embed a tenant's chatbot
            // via /embed/{key}/chat.js; tenant resolved from the embed key.
            Route::middleware('api')->group(base_path('routes/embed.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'module' => \App\Http\Middleware\CheckTenantModule::class,
            'access' => \App\Http\Middleware\CheckUserModuleAccess::class,
            'track.view' => \App\Http\Middleware\TrackPageView::class,
        ]);

        // Guest/auth redirects resolve to the right side based on context.
        $middleware->redirectGuestsTo(
            fn () => tenant() ? route('tenant.login') : route('login')
        );
        $middleware->redirectUsersTo(
            fn () => tenant() ? route('tenant.dashboard') : route('dashboard')
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
