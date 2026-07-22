<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\User;
use DomainException;
use InvalidArgumentException;

class CreditLimitValidator
{
    public function __construct(
        protected CreditPolicy $creditPolicy,
        protected CustomerCreditEligibleSpecification $creditSpecification
    ) {}

    /**
     * Evaluates sales order amount against credit limit.
     * Places the order on credit hold if it exceeds threshold limits or is not eligible.
     */
    public function validateAndApplyHold(SalesOrder $salesOrder): bool
    {
        $customer = $salesOrder->customer;

        // 1. Check customer eligibility specification
        if (! $this->creditSpecification->isSatisfiedBy($customer)) {
            $salesOrder->credit_hold_status = 'hold';
            $salesOrder->save();

            return false;
        }

        // 2. Check total exposure limit via CreditPolicy
        $isWithinLimit = $this->creditPolicy->isWithinCreditLimit(
            $salesOrder->company_id,
            $salesOrder->customer_id,
            (float) $salesOrder->total_amount
        );

        if (! $isWithinLimit) {
            $salesOrder->credit_hold_status = 'hold';
            $salesOrder->save();

            return false;
        }

        $salesOrder->credit_hold_status = 'none';
        $salesOrder->save();

        return true;
    }

    /**
     * Manually overrides and releases a credit hold.
     */
    public function releaseHold(SalesOrder $salesOrder, User $approver, string $reason): void
    {
        if ($salesOrder->credit_hold_status !== 'hold') {
            throw new DomainException('This sales order is not currently on credit hold.');
        }

        $reason = trim($reason);
        if (empty($reason)) {
            throw new InvalidArgumentException('An override reason is required to release the credit hold.');
        }

        // Apply hold release values
        $salesOrder->credit_hold_status = 'released';
        $salesOrder->credit_hold_released_by = $approver->id;
        $salesOrder->credit_hold_released_at = now();
        $salesOrder->credit_hold_override_reason = $reason;
        $salesOrder->save();
    }
}
