<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class StorefrontMaintenance
{
    /**
     * Prevent the public storefront from being accessed when maintenance mode is enabled.
     */
    public function handle(Request $request, Closure $next)
    {
        $maintenanceEnabled = filter_var(
            Setting::query()->where('key', 'maintenance_mode_enabled')->value('value') ?: '0',
            FILTER_VALIDATE_BOOLEAN
        );

        if (! $maintenanceEnabled) {
            return $next($request);
        }

        if ($request->is('admin*') || $request->routeIs('filament.*') || $request->routeIs('admin.*')) {
            return $next($request);
        }

        if (auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'The storefront is temporarily unavailable.'], 503);
        }

        return response()->view('errors.maintenance', [
            'title' => 'Maintenance mode',
            'message' => Setting::query()->where('key', 'maintenance_message')->value('value')
                ?: 'We are improving the store and will be back shortly.',
        ], 503);
    }
}
