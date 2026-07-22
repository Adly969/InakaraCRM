<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureFlag
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $featureCode): Response
    {
        if (app()->bound('current_tenant')) {
            $tenant = app('current_tenant');

            if (! $tenant->featureEnabled($featureCode)) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => "The module '{$featureCode}' is disabled or not included in your active subscription plan.",
                    ], 403);
                }

                abort(403, "The module '{$featureCode}' is disabled or not included in your active subscription plan.");
            }
        }

        return $next($request);
    }
}
