<?php

namespace App\Services;

use App\Models\DeliveryOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Validation\ValidationException;

class DeliveryValidationService
{
    /**
     * Validate that the requested delivery quantities do not exceed the outstanding quantities of the Sales Order.
     *
     * @param  array<int, array{sales_order_item_id: int, quantity_requested: float}>  $items
     *
     * @throws ValidationException
     */
    public function validateOutstandingQuantity(SalesOrder $salesOrder, array $items): void
    {
        if (in_array($salesOrder->status, ['draft', 'cancelled'])) {
            throw ValidationException::withMessages([
                'sales_order_id' => "Cannot create delivery for a Sales Order in {$salesOrder->status} status.",
            ]);
        }

        // Sort items by sales_order_item_id ascending to prevent deadlocks on locking
        usort($items, function ($a, $b) {
            return $a['sales_order_item_id'] <=> $b['sales_order_item_id'];
        });

        foreach ($items as $item) {
            $soItem = SalesOrderItem::where('id', $item['sales_order_item_id'])
                ->lockForUpdate()
                ->first();

            if (! $soItem || $soItem->sales_order_id !== $salesOrder->id) {
                throw ValidationException::withMessages([
                    'items' => "Invalid Sales Order Item ID: {$item['sales_order_item_id']}.",
                ]);
            }

            // Calculate cumulative requested quantities in existing active DOs
            $deliveredQty = DeliveryOrderItem::where('sales_order_item_id', $soItem->id)
                ->whereHas('deliveryOrder', function ($query) {
                    $query->where('status', '!=', 'cancelled');
                })
                ->sum('quantity_requested');

            $outstanding = $soItem->quantity - $deliveredQty;

            if ($item['quantity_requested'] > $outstanding) {
                throw ValidationException::withMessages([
                    'items' => "Requested quantity for item {$soItem->sku} ({$item['quantity_requested']}) exceeds the remaining outstanding quantity ({$outstanding}).",
                ]);
            }
        }
    }
}
