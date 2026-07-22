<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\DeliveryOrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\SalesOrderItem;
use Illuminate\Validation\ValidationException;

class InvoiceValidationService
{
    /**
     * Validate customer credit limit.
     */
    public function validateCreditLimit(int $customerId, float $newInvoiceTotal, ?int $excludeInvoiceId = null): void
    {
        $customer = Customer::findOrFail($customerId);

        // Standard stub credit limit if not present in schema
        $creditLimit = 50000000.00; // 50M IDR

        $query = Invoice::where('customer_id', $customerId)
            ->whereIn('status', ['approved', 'issued', 'overdue']);

        if ($excludeInvoiceId) {
            $query->where('id', '!=', $excludeInvoiceId);
        }

        $existingOutstanding = (float) $query->sum('outstanding_balance');

        if (($existingOutstanding + $newInvoiceTotal) > $creditLimit) {
            throw ValidationException::withMessages([
                'credit_limit' => ["Billing this invoice would breach the customer's credit limit. Current outstanding: {$existingOutstanding}, Limit: {$creditLimit}."],
            ]);
        }
    }

    /**
     * Validate invoice quantities against source reference items.
     */
    public function validateQuantities(array $items, ?int $excludeInvoiceId = null): void
    {
        foreach ($items as $item) {
            $qty = (float) $item['quantity'];

            // 1. If linked to DO item
            if (! empty($item['delivery_order_item_id'])) {
                $doItem = DeliveryOrderItem::findOrFail($item['delivery_order_item_id']);

                $query = InvoiceItem::where('delivery_order_item_id', $doItem->id);
                if ($excludeInvoiceId) {
                    $query->where('invoice_id', '!=', $excludeInvoiceId);
                }

                $alreadyInvoiced = (float) $query->whereHas('invoice', function ($q) {
                    $q->whereIn('status', ['draft', 'approved', 'issued', 'overdue']);
                })->sum('quantity');

                $outstanding = (float) $doItem->quantity_requested - $alreadyInvoiced;

                if ($qty > $outstanding) {
                    throw ValidationException::withMessages([
                        'items' => ["Invoiced quantity for item {$doItem->sku} cannot exceed the outstanding DO quantity of {$outstanding}."],
                    ]);
                }
            }

            // 2. If linked directly to SO item
            if (! empty($item['sales_order_item_id'])) {
                $soItem = SalesOrderItem::findOrFail($item['sales_order_item_id']);

                $query = InvoiceItem::where('sales_order_item_id', $soItem->id);
                if ($excludeInvoiceId) {
                    $query->where('invoice_id', '!=', $excludeInvoiceId);
                }

                $alreadyInvoiced = (float) $query->whereHas('invoice', function ($q) {
                    $q->whereIn('status', ['draft', 'approved', 'issued', 'overdue']);
                })->sum('quantity');

                $outstanding = (float) $soItem->quantity - $alreadyInvoiced;

                if ($qty > $outstanding) {
                    throw ValidationException::withMessages([
                        'items' => ["Invoiced quantity for item {$soItem->sku} cannot exceed the outstanding SO quantity of {$outstanding}."],
                    ]);
                }
            }
        }
    }
}
