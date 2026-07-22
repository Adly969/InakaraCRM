<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Invoice;

class CustomerBalanceRepository
{
    /**
     * Get the active credit limit of a customer (standard ERP stub).
     */
    public function getCreditLimit(int $customerId): float
    {
        // Future: customer.credit_limit from customers table.
        // For now, return standard 50M IDR as per validation specifications.
        return 50000000.00;
    }

    /**
     * Get the sum of outstanding balances for all active customer invoices.
     */
    public function getOutstandingBalance(int $customerId): float
    {
        return (float) Invoice::where('customer_id', $customerId)
            ->whereIn('status', ['issued', 'overdue'])
            ->sum('outstanding_balance');
    }
}
