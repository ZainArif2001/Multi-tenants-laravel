<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserModuleAccess
{
    /**
     * Block users who haven't been granted this module by their
     * tenant admin. Usage: ->middleware('access:posts')
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        abort_unless($request->user()?->hasAccessTo($module), 404);

        return $next($request);
    }
}
