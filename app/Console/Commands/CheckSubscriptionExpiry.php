<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class CheckSubscriptionExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks and updates status of expired subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredSubscriptions = Subscription::where('status', '!=', 'expired')
            ->where('ends_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredSubscriptions as $subscription) {
            $subscription->update([
                'status' => 'expired',
            ]);

            cache()->forget("tenant:{$subscription->tenant_id}:flags");
            $count++;
        }

        $this->info("Successfully updated {$count} expired tenant subscriptions.");
    }
}
