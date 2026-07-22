<?php

namespace App\Services;

interface AccountingGatewayInterface
{
    /**
     * Post a transaction to the general ledger using configured posting rules.
     *
     * @param  string  $eventType  e.g. SALES_INVOICE_APPROVED, CUSTOMER_PAYMENT_RECEIVED
     * @param  array  $payload  Operational data with amounts, company_id, branch_id, dimensions
     * @return string Generated Journal Number
     */
    public function postTransaction(string $eventType, array $payload): string;
}
