<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->bound('current_tenant')) {
            $tenant = app('current_tenant');
            $subscription = $tenant->subscription;

            if (! $subscription || ! $subscription->isActive()) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your subscription has expired or has been suspended.',
                    ], 403);
                }

                return redirect()->route('subscription.inactive');
            }
        }

        return $next($request);
    }
}
