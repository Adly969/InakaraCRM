<?php

namespace App\Jobs;

use App\Models\FinancialEvent;
use App\Models\PostingFailure;
use App\Models\PostingJob;
use App\Services\AccountingGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessFinancialEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $eventId,
        protected int $postingJobId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AccountingGateway $gateway): void
    {
        $postingJob = PostingJob::find($this->postingJobId);
        if (! $postingJob || $postingJob->status === 'SUCCESS') {
            return;
        }

        $postingJob->update(['status' => 'PROCESSING']);
        $event = FinancialEvent::find($this->eventId);

        try {
            $gateway->postEvent($event->event_type, $event->payload, $event->idempotency_key);
            $postingJob->update([
                'status' => 'SUCCESS',
                'processed_at' => now(),
            ]);
        } catch (Throwable $e) {
            $postingJob->increment('retry_count');
            $postingJob->update(['error_message' => $e->getMessage()]);

            if ($postingJob->retry_count >= $postingJob->max_retries) {
                $postingJob->update(['status' => 'FAILED']);

                // Route to DLQ (posting_failures table)
                PostingFailure::create([
                    'job_id' => $postingJob->id,
                    'event_type' => $event->event_type,
                    'failed_payload' => $event->payload,
                    'failure_reason' => $e->getMessage(),
                    'stack_trace' => $e->getTraceAsString(),
                ]);
            } else {
                // Requeue job with backoff delay
                $this->release(30 * $postingJob->retry_count);
            }
        }
    }
}
