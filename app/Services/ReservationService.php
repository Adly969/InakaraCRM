<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryReservation;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    protected CanReserveInventorySpecification $canReserveSpec;

    public function __construct(
        protected InventoryProjectionService $projectionService,
        ?CanReserveInventorySpecification $canReserveSpec = null
    ) {
        $this->canReserveSpec = $canReserveSpec ?? app(CanReserveInventorySpecification::class);
    }

    /**
     * Create stock reservations for Sales Order items.
     */
    public function reserveStock(SalesOrder $so): void
    {
        DB::transaction(function () use ($so) {
            $defaultWh = Warehouse::where('is_default', true)->first() ?: Warehouse::first();
            if (! $defaultWh) {
                throw ValidationException::withMessages([
                    'warehouse' => ['No active warehouse found to associate inventory reservation.'],
                ]);
            }

            foreach ($so->items as $soItem) {
                // Generate consistent SKU based on description
                $sku = strtoupper(implode('-', array_filter(explode(' ', preg_replace('/[^a-zA-Z0-9 ]/', '', $soItem->description)))));
                if (empty($sku)) {
                    $sku = 'ITEM-'.str_pad($soItem->id ?? rand(100, 999), 3, '0', STR_PAD_LEFT);
                }

                $item = InventoryItem::where('warehouse_id', $defaultWh->id)
                    ->where('sku', $sku)
                    ->lockForUpdate()
                    ->first();

                if (! $item) {
                    $item = InventoryItem::create([
                        'warehouse_id' => $defaultWh->id,
                        'sku' => $sku,
                        'name' => $soItem->description, // Fallback description as name
                        'description' => $soItem->description,
                        'quantity_current' => 0.00,
                        'quantity_reserved' => 0.00,
                        'avg_cost_price' => 0.00,
                    ]);
                }

                // Check physical availability via Specification
                if (! $this->canReserveSpec->isSatisfiedBy($item, (float) $soItem->quantity)) {
                    throw ValidationException::withMessages([
                        'inventory' => ["Insufficient inventory available to reserve quantity of {$soItem->quantity} for SKU {$sku}."],
                    ]);
                }

                // Create reservation entry
                InventoryReservation::create([
                    'sales_order_id' => $so->id,
                    'inventory_item_id' => $item->id,
                    'quantity_reserved' => $soItem->quantity,
                    'quantity_released' => 0.00,
                    'status' => 'active',
                ]);

                // Update projection cache
                $newReserved = (float) $item->quantity_reserved + (float) $soItem->quantity;
                $this->projectionService->updateProjection($item, $item->quantity_current, $newReserved);
            }
        }, 3);
    }

    /**
     * Release a specific quantity of reservation.
     */
    public function releaseReservation(SalesOrder $so, InventoryItem $item, float $qty): void
    {
        DB::transaction(function () use ($so, $item, $qty) {
            $reservation = InventoryReservation::where('sales_order_id', $so->id)
                ->where('inventory_item_id', $item->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (! $reservation) {
                return;
            }

            $item->lockForUpdate();

            $released = (float) $reservation->quantity_released + $qty;
            $status = $released >= (float) $reservation->quantity_reserved ? 'released' : 'active';

            $reservation->update([
                'quantity_released' => $released,
                'status' => $status,
            ]);

            $newReserved = max(0.00, (float) $item->quantity_reserved - $qty);
            $this->projectionService->updateProjection($item, $item->quantity_current, $newReserved);
        }, 3);
    }

    /**
     * Cancel reservations for a Sales Order.
     */
    public function cancelReservations(SalesOrder $so): void
    {
        DB::transaction(function () use ($so) {
            $reservations = InventoryReservation::where('sales_order_id', $so->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->get();

            foreach ($reservations as $reservation) {
                $item = $reservation->inventoryItem()->lockForUpdate()->first();
                if (! $item) {
                    continue;
                }

                $remainingQty = (float) $reservation->quantity_reserved - (float) $reservation->quantity_released;
                $reservation->update(['status' => 'cancelled']);

                $newReserved = max(0.00, (float) $item->quantity_reserved - $remainingQty);
                $this->projectionService->updateProjection($item, $item->quantity_current, $newReserved);
            }
        }, 3);
    }
}
