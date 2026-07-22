<?php

namespace App\Jobs\CRM;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DuplicateDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Simple background scanner looking for overlapping email/phone values
        $duplicates = Customer::select('email')
            ->whereNotNull('email')
            ->groupBy('email', 'tenant_id')
            ->havingRaw('COUNT(id) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            Log::warning("CRM Duplicate detected: Multiple customer profiles exist under the email '{$dup->email}'");
        }
    }
}
