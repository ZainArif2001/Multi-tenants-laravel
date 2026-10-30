<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;

/**
 * Records public-site traffic — one row per page view.
 * Visitor identity is a privacy-safe hash of IP + user agent.
 */
class TrackPageView
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->isMethod('GET') && $response->isOk()) {
            try {
                PageView::create([
                    'path' => '/'.ltrim($request->path(), '/'),
                    'visitor_hash' => sha1($request->ip().'|'.substr((string) $request->userAgent(), 0, 200)),
                    'created_at' => now(),
                ]);
            } catch (\Throwable) {
                // Analytics must never break a page render
            }
        }

        return $response;
    }
}
