<?php

namespace App\Services;

use App\Exceptions\AllocationExceededException;
use App\Exceptions\InvoiceLockedException;
use App\Repositories\InvoiceRepository;

class PaymentAllocationService
{
    public function __construct(
        protected InvoiceRepository $invoiceRepository
    ) {}

    /**
     * Validate allocation distributions.
     *
     * @param  array<int, array{invoice_id: int, amount: float}>  $allocations
     *
     * @throws AllocationExceededException
     * @throws InvoiceLockedException
     */
    public function validateAllocations(array $allocations, float $paymentAmount): void
    {
        $allocatedSum = 0.00;
        $seenInvoices = [];

        foreach ($allocations as $alloc) {
            $invoiceId = $alloc['invoice_id'];
            $amount = (float) $alloc['amount'];

            if ($amount <= 0) {
                throw new AllocationExceededException('Allocation amount must be greater than zero.');
            }

            if (in_array($invoiceId, $seenInvoices)) {
                throw new AllocationExceededException('Duplicate invoice allocation detected.');
            }
            $seenInvoices[] = $invoiceId;

            // Fetch invoice
            $invoice = $this->invoiceRepository->find($invoiceId);

            if (! in_array($invoice->status->value, ['issued', 'overdue'])) {
                throw new InvoiceLockedException("Invoice {$invoice->reference_no} is not open for payment allocation.");
            }

            if ($amount > (float) $invoice->outstanding_balance) {
                throw new AllocationExceededException(
                    "Allocation amount {$amount} exceeds outstanding balance {$invoice->outstanding_balance} on invoice {$invoice->reference_no}."
                );
            }

            $allocatedSum += $amount;
        }

        if ($allocatedSum > $paymentAmount) {
            throw new AllocationExceededException("Total allocated amount {$allocatedSum} cannot exceed payment amount {$paymentAmount}.");
        }
    }
}
