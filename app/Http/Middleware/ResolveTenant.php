<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = null;

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->tenant_id) {
                $tenant = Tenant::find($user->tenant_id);
            }
        }

        if (! $tenant) {
            $slug = $request->route('tenant_slug') ?? $request->header('X-Tenant-Slug');
            if ($slug) {
                $tenant = Tenant::where('slug', $slug)->first();
            }
        }

        if ($tenant) {
            app()->instance('current_tenant', $tenant);

            if (config('permission.teams')) {
                setPermissionsTeamId($tenant->id);
            }
        }

        return $next($request);
    }
}
