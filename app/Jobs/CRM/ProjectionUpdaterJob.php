<?php

namespace App\Jobs\CRM;

use App\Models\CrmDashboardProjection;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProjectionUpdaterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected string $tenantId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Calculate active customers count
        $activeCustomersCount = Customer::where('tenant_id', $this->tenantId)
            ->where('status', 'active')
            ->count();

        CrmDashboardProjection::updateOrCreate(
            ['tenant_id' => $this->tenantId, 'metric_key' => 'active_customers'],
            ['metric_value' => $activeCustomersCount, 'last_updated_at' => now()]
        );

        // 2. Calculate unconverted leads count
        $unconvertedLeadsCount = Lead::where('tenant_id', $this->tenantId)
            ->where('status', '!=', 'converted')
            ->count();

        CrmDashboardProjection::updateOrCreate(
            ['tenant_id' => $this->tenantId, 'metric_key' => 'unconverted_leads'],
            ['metric_value' => $unconvertedLeadsCount, 'last_updated_at' => now()]
        );
    }
}
