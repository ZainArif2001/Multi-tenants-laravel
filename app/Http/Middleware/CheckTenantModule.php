<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantModule
{
    /**
     * Block access to modules the tenant's plan doesn't include.
     * Usage: ->middleware('module:posts')
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        abort_unless(tenant()->hasModule($module), 404);

        return $next($request);
    }
}
