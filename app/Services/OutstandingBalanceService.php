<?php

namespace App\Services;

use App\Exceptions\OutstandingBalanceException;
use App\Models\Invoice;

class OutstandingBalanceService
{
    /**
     * Deduct outstanding balance from an invoice atomically.
     *
     * @throws OutstandingBalanceException
     */
    public function deductBalance(Invoice $invoice, float $amount): void
    {
        $lockedInvoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();
        $currentBalance = (float) $lockedInvoice->outstanding_balance;
        $newBalance = round($currentBalance - $amount, 2);

        if ($newBalance < 0) {
            throw new OutstandingBalanceException("Deduction of {$amount} would make outstanding balance negative on invoice {$invoice->id}.");
        }

        $lockedInvoice->outstanding_balance = $newBalance;
        $lockedInvoice->save();

        // ponytail: Collection workflow trigger — EXT-10-005. Trigger reminders if balance changes.
    }

    /**
     * Restore outstanding balance to an invoice atomically.
     *
     * @throws OutstandingBalanceException
     */
    public function restoreBalance(Invoice $invoice, float $amount): void
    {
        $lockedInvoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();
        $currentBalance = (float) $lockedInvoice->outstanding_balance;
        $totalAmount = (float) $lockedInvoice->total_amount;
        $newBalance = round($currentBalance + $amount, 2);

        if ($newBalance > $totalAmount) {
            throw new OutstandingBalanceException("Reversal of {$amount} would make outstanding balance {$newBalance} exceed total amount {$totalAmount} on invoice {$invoice->id}.");
        }

        $lockedInvoice->outstanding_balance = $newBalance;
        $lockedInvoice->save();
    }

    /**
     * Sum customer outstanding balances.
     */
    public function getCustomerOutstanding(int $customerId): float
    {
        return (float) Invoice::where('customer_id', $customerId)
            ->whereIn('status', ['issued', 'overdue'])
            ->sum('outstanding_balance');
    }
}
