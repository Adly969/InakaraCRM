<?php

namespace App\Services;

use App\Jobs\ProcessFinancialEventJob;
use App\Models\IdempotencyKey;
use App\Models\PostingFailure;
use Illuminate\Support\Facades\DB;

class EventReplayEngine
{
    public function __construct(
        protected PostingRuleSimulator $simulator
    ) {}

    /**
     * Replay a failed event based on selected mode.
     *
     * @param  string  $mode  DRY_RUN, SANDBOX, or PRODUCTION
     * @param  int  $userId  Required for audit trace in PRODUCTION
     * @return array Replay results
     */
    public function replay(int $failureId, string $mode, int $userId): array
    {
        $failure = PostingFailure::findOrFail($failureId);
        $job = $failure->job;
        $event = $job->event;

        if ($mode === 'DRY_RUN') {
            $projection = $this->simulator->simulate($event->event_type, $event->payload);

            return [
                'mode' => 'DRY_RUN',
                'success' => true,
                'projection' => $projection,
            ];
        }

        if ($mode === 'SANDBOX') {
            return DB::transaction(function () use ($event) {
                $projection = $this->simulator->simulate($event->event_type, $event->payload);
                DB::rollBack();

                return [
                    'mode' => 'SANDBOX',
                    'success' => true,
                    'projection' => $projection,
                ];
            });
        }

        // PRODUCTION Replay
        return DB::transaction(function () use ($failure, $job, $event, $userId) {
            // Remove previous idempotency key to bypass unique constraint checks on replay
            IdempotencyKey::where('key_hash', hash('sha256', $event->idempotency_key))->delete();

            $job->update([
                'status' => 'PENDING',
                'retry_count' => 0,
                'error_message' => null,
            ]);

            $failure->update([
                'is_resolved' => true,
                'resolved_by' => $userId,
                'resolved_at' => now(),
            ]);

            // Re-dispatch queue job
            ProcessFinancialEventJob::dispatch($event->id, $job->id);

            return [
                'mode' => 'PRODUCTION',
                'success' => true,
                'message' => 'Job dispatched for processing in production queue.',
            ];
        });
    }
}
