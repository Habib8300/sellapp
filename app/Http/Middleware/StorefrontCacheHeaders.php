<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StorefrontCacheHeaders
{
    /**
     * Add browser cache hints to public storefront pages to reduce repeat page loads.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (
            ! $request->isMethod('GET')
            || $request->is('admin*')
            || $request->routeIs('admin.*')
            || $request->routeIs('blog.*')
        ) {
            return $response;
        }

        if ($request->routeIs('home')) {
            $response->headers->set('Cache-Control', 'private, no-store, no-cache, max-age=0, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');

            return $response;
        }

        $response->headers->set('Cache-Control', 'max-age=300, public, stale-while-revalidate=600');

        return $response;
    }
}
