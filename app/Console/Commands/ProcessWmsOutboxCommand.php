<?php

namespace App\Console\Commands;

use App\Models\SalesEventOutbox;
use App\Services\AccountingGateway;
use App\Services\OpenTelemetryTracker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessWmsOutboxCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wms:outbox:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches WMS inventory outbox events to the Accounting Gateway';

    /**
     * Execute the console command.
     */
    public function handle(AccountingGateway $gateway, OpenTelemetryTracker $tracker): int
    {
        $pendingEvents = SalesEventOutbox::where('is_dispatched', false)
            ->whereIn('event_type', [
                'InventoryReceived',
                'InventoryReserved',
                'InventoryReleased',
                'InventoryAdjusted',
                'InventoryTransferred',
                'InventoryConsumed',
                'InventoryPicked',
                'InventoryPacked',
                'InventoryShipped',
                'InventoryReturned',
                'InventoryWrittenOff',
                'InventoryRevalued',
                'InventoryCountAdjusted',
            ])
            ->orderBy('id', 'asc')
            ->get();

        if ($pendingEvents->isEmpty()) {
            $this->info('No pending WMS outbox events.');

            return Command::SUCCESS;
        }

        $this->info("Processing {$pendingEvents->count()} pending WMS events...");

        foreach ($pendingEvents as $event) {
            $span = $tracker->startSpan('wms_outbox_dispatch', $event->correlation_id);

            try {
                $payload = $event->payload;

                // Merge trace and context headers
                $payload['correlation_id'] = $event->correlation_id;
                $payload['causation_id'] = $event->causation_id;
                $payload['trace_id'] = $event->trace_id;
                $payload['idempotency_key'] = $event->idempotency_key;
                $payload['company_id'] = $event->company_id;

                $payload['branch_id'] = $payload['branch_id'] ?? 1;
                $payload['user_id'] = $payload['user_id'] ?? 1;

                $this->info("Posting WMS event [{$event->event_type}] with ID [{$event->event_id}]");

                $gateway->postEvent($event->event_type, $payload, $event->idempotency_key);

                $event->is_dispatched = true;
                $event->save();

                $this->info("WMS Event [{$event->event_id}] dispatched successfully.");
            } catch (\Throwable $e) {
                $this->error("Failed to dispatch WMS event [{$event->event_id}]: ".$e->getMessage());
                Log::error("WMS Outbox Dispatch Failure [Event ID: {$event->event_id}]: ".$e->getMessage(), [
                    'exception' => $e,
                ]);
            } finally {
                $tracker->endSpan($span);
            }
        }

        $this->info('WMS outbox processing completed.');

        return Command::SUCCESS;
    }
}
