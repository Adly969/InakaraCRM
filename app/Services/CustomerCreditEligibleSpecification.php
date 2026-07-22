<?php

namespace App\Services;

use App\Models\Customer;

class CustomerCreditEligibleSpecification
{
    /**
     * Checks if the customer has an active status and is eligible for new credit transactions.
     */
    public function isSatisfiedBy(Customer $customer): bool
    {
        // 1. Customer status must be active
        if ($customer->status->value !== 'active') {
            return false;
        }

        // 2. Customer type checks (e.g. block credit sales for cash-only walk-in retail customers)
        if ($customer->type === 'walk-in') {
            return false;
        }

        return true;
    }
}
