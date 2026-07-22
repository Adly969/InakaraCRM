<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SalesEventOutbox;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReturnService
{
    public function __construct(
        protected ReturnPolicy $returnPolicy
    ) {}

    /**
     * Creates a Return Material Authorization (RMA) record and registers it.
     */
    public function createRma(Invoice $invoice, array $itemsData, User $creator): array
    {
        return DB::transaction(function () use ($invoice, $itemsData, $creator) {
            // Check return eligibility window (e.g. maximum 30 days since invoice creation)
            if ($invoice->created_at->addDays(30)->isPast()) {
                throw ValidationException::withMessages([
                    'invoice' => ['Invoice return window of 30 days has expired.'],
                ]);
            }

            $rmaNumber = 'RMA-'.now()->format('Ymd').'-'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $totalReturnedValue = 0.00;

            foreach ($itemsData as $item) {
                // Find matching invoice line item
                $price = (float) ($item['unit_price'] ?? 0.00);
                $qty = (float) ($item['quantity'] ?? 1.00);
                $totalReturnedValue += ($qty * $price);
            }

            $restockingFee = $this->returnPolicy->calculateRestockingFee($totalReturnedValue, $itemsData[0]['condition'] ?? 'GOOD');
            $creditMemoAmount = $totalReturnedValue - $restockingFee;

            // Save outbox event for the RMA Creation
            $eventId = (string) Str::uuid();
            $correlationId = (string) Str::uuid();
            SalesEventOutbox::create([
                'event_id' => $eventId,
                'company_id' => $invoice->company_id,
                'event_type' => 'ReturnReceived',
                'payload' => [
                    'invoice_id' => $invoice->id,
                    'customer_id' => $invoice->customer_id,
                    'amount' => $totalReturnedValue,
                    'restocking_fee' => $restockingFee,
                    'credit_memo_amount' => $creditMemoAmount,
                    'rma_number' => $rmaNumber,
                    'user_id' => $creator->id,
                    'schema_version' => 1,
                ],
                'correlation_id' => $correlationId,
                'causation_id' => $eventId,
                'trace_id' => (string) Str::uuid(),
                'idempotency_key' => 'idem-rma-'.$rmaNumber,
                'is_dispatched' => false,
            ]);

            return [
                'rma_number' => $rmaNumber,
                'returned_value' => $totalReturnedValue,
                'restocking_fee' => $restockingFee,
                'credit_memo_amount' => $creditMemoAmount,
            ];
        });
    }
}
