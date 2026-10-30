<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

/**
 * Resolves the tenant from the public embed key on /embed/{key}/* routes.
 * These endpoints are called cross-origin from external websites, so they
 * are stateless (no session/CSRF) and CORS-enabled.
 */
class ResolveEmbedTenant
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('OPTIONS')) {
            return response('', 204)->withHeaders($this->cors());
        }

        $tenant = Tenant::where('data->embed_key', $request->route('key'))->first();

        abort_unless($tenant && ! $tenant->suspended && $tenant->hasModule('chat'), 404);

        tenancy()->initialize($tenant);

        $response = $next($request);

        foreach ($this->cors() as $k => $v) {
            $response->headers->set($k, $v);
        }

        return $response;
    }

    protected function cors(): array
    {
        return [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Accept',
        ];
    }
}
