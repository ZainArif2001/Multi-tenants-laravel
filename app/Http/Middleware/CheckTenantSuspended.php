<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantSuspended
{
    /**
     * Block all requests on a suspended tenant's domain (except the
     * suspended notice itself — it's a static abort anyway).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (tenant() && tenant()->suspended) {
            abort(503, 'This workspace has been suspended. Please contact support.');
        }

        return $next($request);
    }
}
