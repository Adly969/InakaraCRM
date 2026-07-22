<?php

namespace App\Jobs\Middleware;

use App\Models\Tenant;

class TenantAwareJobMiddleware
{
    /**
     * Process the queued job.
     *
     * @param  mixed  $job
     * @param  callable  $next
     * @return mixed
     */
    public function handle($job, $next)
    {
        $tenantId = null;

        if (property_exists($job, 'tenantId')) {
            $tenantId = $job->tenantId;
        } elseif (isset($job->tenant_id)) {
            $tenantId = $job->tenant_id;
        }

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                app()->instance('current_tenant', $tenant);

                if (config('permission.teams')) {
                    setPermissionsTeamId($tenant->id);
                }
            }
        }

        return $next($job);
    }
}
