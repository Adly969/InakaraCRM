<?php

namespace App\Services;

use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\CustomerContract;
use App\Models\SalesOrder;

class CreditPolicy
{
    /**
     * Checks if the customer has sufficient credit left for the proposed order total.
     */
    public function isWithinCreditLimit(int $companyId, int $customerId, float $proposedAmount): bool
    {
        $limit = $this->resolveCreditLimit($companyId, $customerId);

        if ($limit === null) {
            // Null credit limit means unlimited/default credit allowed
            return true;
        }

        $exposure = $this->calculateExposure($companyId, $customerId);

        return ($exposure + $proposedAmount) <= $limit;
    }

    /**
     * Resolves active contract credit limit override or default limit.
     */
    public function resolveCreditLimit(int $companyId, int $customerId): ?float
    {
        $contract = CustomerContract::where('company_id', $companyId)
            ->where('customer_id', $customerId)
            ->where('status', 'ACTIVE')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        if ($contract && $contract->credit_limit_override !== null) {
            return (float) $contract->credit_limit_override;
        }

        // Default company credit limit (e.g. 50,000,000 IDR)
        return 50000000.00;
    }

    /**
     * Calculates customer credit exposure (unpaid invoices + active orders).
     */
    public function calculateExposure(int $companyId, int $customerId): float
    {
        // 1. Calculate unpaid/open invoice totals
        // For simplicity in Sprint 14, retrieve unresolved sales invoices or outstanding balance metrics
        $outstandingReceivable = app(OutstandingBalanceService::class)->getCustomerOutstanding($customerId);

        // 2. Add total amount of confirmed/reserved/in-preparation orders that are not yet billed
        $activeOrdersTotal = SalesOrder::where('company_id', $companyId)
            ->where('customer_id', $customerId)
            ->whereIn('status', [
                SalesOrderStatus::Confirmed,
                SalesOrderStatus::PendingApproval,
                SalesOrderStatus::Approved,
                SalesOrderStatus::Reserved,
                SalesOrderStatus::InPreparation,
            ])
            ->sum('total_amount');

        return (float) ($outstandingReceivable + $activeOrdersTotal);
    }
}
