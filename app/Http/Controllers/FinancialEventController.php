<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessFinancialEventJob;
use App\Models\FinancialEvent;
use App\Models\IdempotencyKey;
use App\Models\PostingFailure;
use App\Models\PostingJob;
use App\Services\AccountingGateway;
use App\Services\EventReplayEngine;
use App\Services\PostingRuleSimulator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinancialEventController extends Controller
{
    public function __construct(
        protected AccountingGateway $gateway,
        protected PostingRuleSimulator $simulator,
        protected EventReplayEngine $replayEngine
    ) {}

    /**
     * Ingests a business event asynchronously (pushes to Redis/RabbitMQ queue via jobs).
     */
    public function ingestEvent(Request $request): JsonResponse
    {
        $request->validate([
            'event_type' => 'required|string|max:100',
            'company_id' => 'required|integer',
            'branch_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'user_id' => 'required|integer',
            'payload' => 'required|array',
            'idempotency_key' => 'required|string|max:64',
        ]);

        $companyId = $request->input('company_id');
        $idempotencyKey = $request->input('idempotency_key');
        $hashKey = hash('sha256', $idempotencyKey);

        // Deduplication check
        $exists = IdempotencyKey::where('key_hash', $hashKey)->exists();
        if ($exists) {
            return response()->json([
                'message' => 'Duplicate transaction payload detected (Idempotency Violation).',
            ], 422);
        }

        $event = DB::transaction(function () use ($request, $idempotencyKey) {
            $eventUuid = (string) Str::uuid();
            $correlationId = $request->input('payload.correlation_id') ?? (string) Str::uuid();

            // Construct payload with required integration details
            $payload = array_merge($request->input('payload'), [
                'company_id' => $request->input('company_id'),
                'branch_id' => $request->input('branch_id'),
                'amount' => $request->input('amount'),
                'user_id' => $request->input('user_id'),
                'transaction_date' => $request->input('payload.transaction_date') ?? now()->toDateString(),
            ]);

            $event = FinancialEvent::create([
                'event_uuid' => $eventUuid,
                'company_id' => $request->input('company_id'),
                'branch_id' => $request->input('branch_id'),
                'event_type' => $request->input('event_type'),
                'source_module' => $request->input('payload.source_module') ?? 'UNKNOWN',
                'payload' => $payload,
                'status' => 'QUEUED',
                'idempotency_key' => $idempotencyKey,
                'correlation_id' => $correlationId,
            ]);

            $job = PostingJob::create([
                'event_id' => $event->id,
                'status' => 'PENDING',
                'retry_count' => 0,
                'max_retries' => 3,
            ]);

            // Dispatch to the async queue
            ProcessFinancialEventJob::dispatch($event->id, $job->id);

            return $event;
        });

        return response()->json([
            'message' => 'Event successfully ingested and queued.',
            'event_uuid' => $event->event_uuid,
            'status' => 'QUEUED',
        ], 202);
    }

    /**
     * Dry-runs a dynamic posting rule simulation.
     */
    public function simulate(Request $request): JsonResponse
    {
        $request->validate([
            'event_type' => 'required|string',
            'payload' => 'required|array',
        ]);

        try {
            $result = $this->simulator->simulate(
                $request->input('event_type'),
                $request->input('payload')
            );

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Replay a failed DLQ transaction.
     */
    public function replay(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'mode' => 'required|string|in:DRY_RUN,SANDBOX,PRODUCTION',
        ]);

        $user = Auth::user();
        $result = $this->replayEngine->replay($id, $request->input('mode'), $user ? $user->id : 1);

        return response()->json($result);
    }

    /**
     * Health check and integration metrics dashboard support.
     */
    public function health(): JsonResponse
    {
        $totalEvents = FinancialEvent::count();
        $queuedJobs = PostingJob::where('status', 'PENDING')->count();
        $failedJobs = PostingJob::where('status', 'FAILED')->count();
        $activeFailures = PostingFailure::where('is_resolved', false)->count();

        return response()->json([
            'status' => 'HEALTHY',
            'metrics' => [
                'total_events_processed' => $totalEvents,
                'queued_jobs_depth' => $queuedJobs,
                'failed_jobs_count' => $failedJobs,
                'dlq_active_count' => $activeFailures,
            ],
        ]);
    }
}
