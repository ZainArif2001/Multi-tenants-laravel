<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Per-tenant UI customization — if a view file exists under
 * resources/views/tenants/{tenant_id}/{view-path}.blade.php it is used
 * INSTEAD of the default view. Other tenants keep the default.
 *
 * Example: resources/views/tenants/{id}/app/posts/index.blade.php
 * overrides app.posts.index only for that tenant.
 */
class TenantViewOverrides
{
    public function handle(Request $request, Closure $next): Response
    {
        if (tenant()) {
            $path = resource_path('views/tenants/' . tenant()->id);
            if (is_dir($path)) {
                View::getFinder()->prependLocation($path);
            }
        }

        return $next($request);
    }
}
