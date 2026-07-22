<?php

namespace App\Console\Commands;

use App\Models\SalesEventOutbox;
use App\Services\AccountingGateway;
use App\Services\OpenTelemetryTracker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessSalesOutboxCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:outbox:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches pending events in the sales outbox to the Accounting Gateway';

    /**
     * Execute the console command.
     */
    public function handle(AccountingGateway $gateway, OpenTelemetryTracker $tracker): int
    {
        $pendingEvents = SalesEventOutbox::where('is_dispatched', false)
            ->orderBy('id', 'asc')
            ->get();

        if ($pendingEvents->isEmpty()) {
            $this->info('No pending events in the sales outbox.');

            return Command::SUCCESS;
        }

        $this->info("Processing {$pendingEvents->count()} pending events...");

        foreach ($pendingEvents as $event) {
            $span = $tracker->startSpan('sales_outbox_dispatch', $event->correlation_id);

            try {
                $payload = $event->payload;

                // Merge Correlation, Causation, Trace, and Tenant details into the payload
                $payload['correlation_id'] = $event->correlation_id;
                $payload['causation_id'] = $event->causation_id;
                $payload['trace_id'] = $event->trace_id;
                $payload['idempotency_key'] = $event->idempotency_key;
                $payload['company_id'] = $event->company_id;

                // Add default branch_id and user_id if not present
                $payload['branch_id'] = $payload['branch_id'] ?? 1;
                $payload['user_id'] = $payload['user_id'] ?? 1;

                $this->info("Posting event [{$event->event_type}] with ID [{$event->event_id}]");

                // Dispatch to the Accounting Gateway
                $gateway->postEvent($event->event_type, $payload, $event->idempotency_key);

                $event->is_dispatched = true;
                $event->save();

                $this->info("Event [{$event->event_id}] dispatched successfully.");
            } catch (\Throwable $e) {
                $this->error("Failed to dispatch event [{$event->event_id}]: ".$e->getMessage());
                Log::error("Sales Outbox Dispatch Failure [Event ID: {$event->event_id}]: ".$e->getMessage(), [
                    'exception' => $e,
                ]);
                // In enterprise systems, we do not throw here so other events in the batch can continue to post
            } finally {
                $tracker->endSpan($span);
            }
        }

        $this->info('Sales outbox processing completed.');

        return Command::SUCCESS;
    }
}
