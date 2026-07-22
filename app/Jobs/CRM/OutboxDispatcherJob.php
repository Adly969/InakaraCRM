<?php

namespace App\Jobs\CRM;

use App\Models\CrmEventOutbox;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class OutboxDispatcherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Fetch pending outbox records
        $pendingEvents = CrmEventOutbox::whereNull('processed_at')
            ->orderBy('id', 'asc')
            ->limit(50)
            ->get();

        foreach ($pendingEvents as $outbox) {
            $eventType = $outbox->event_type;
            $payload = $outbox->payload;

            // Dispatch event dynamically
            if (class_exists($eventType)) {
                // If event class exists, we can dispatch it.
                // For simplicity in native Laravel, we dispatch a generic event or class instantiation.
                Event::dispatch(new $eventType($payload));
            }

            // Mark as processed
            $outbox->processed_at = now();
            $outbox->save();
        }
    }
}
