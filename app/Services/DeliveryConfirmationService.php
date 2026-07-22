<?php

namespace App\Services;

use App\Events\DeliveryCompleted;
use App\Events\ShipmentDelivered;
use App\Models\DeliveryEvent;
use App\Models\DeliveryOrderItem;
use App\Models\InventoryItem;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class DeliveryConfirmationService
{
    public function __construct(
        protected InventoryTransactionService $inventoryTxService
    ) {}

    /**
     * Confirm shipment delivery.
     */
    public function confirmDelivery(Shipment $shipment, array $data): void
    {
        DB::transaction(function () use ($shipment, $data) {
            if ($shipment->status !== 'in_transit') {
                throw new \InvalidArgumentException('Only in transit shipments can be confirmed as delivered.');
            }

            $shipment->update([
                'status' => 'delivered',
                'actual_cost' => $data['actual_cost'] ?? $shipment->estimated_cost,
                'actual_delivery_date' => now()->format('Y-m-d'),
            ]);

            $do = $shipment->deliveryOrder;

            // Sort and update delivered quantities on DO items
            $shipmentItems = $shipment->items()->orderBy('delivery_order_item_id')->get();

            foreach ($shipmentItems as $shItem) {
                $doItem = DeliveryOrderItem::where('id', $shItem->delivery_order_item_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $doItem->increment('quantity_delivered', $shItem->quantity_shipped);
            }

            // Recalculate DO status
            $allDelivered = true;
            $anyDelivered = false;
            $doItems = DeliveryOrderItem::where('delivery_order_id', $do->id)->get();
            foreach ($doItems as $item) {
                if ($item->quantity_delivered < $item->quantity_requested) {
                    $allDelivered = false;
                }
                if ($item->quantity_delivered > 0) {
                    $anyDelivered = true;
                }
            }

            $newDoStatus = 'shipped';
            if ($allDelivered) {
                $newDoStatus = 'delivered';
            } elseif ($anyDelivered) {
                $newDoStatus = 'partially_delivered';
            }

            $do->update([
                'status' => $newDoStatus,
                'delivered_by' => Auth::id() ?? 1,
                'delivered_at' => now(),
            ]);

            // Log immutable event with telemetry
            DeliveryEvent::create([
                'delivery_order_id' => $do->id,
                'shipment_id' => $shipment->id,
                'event_type' => 'arrived',
                'event_data' => [
                    'status' => 'delivered',
                    'receiver_name' => $data['receiver_name'],
                    'receiver_signature' => $data['receiver_signature'],
                    'gps_latitude' => $data['gps_latitude'] ?? null,
                    'gps_longitude' => $data['gps_longitude'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_by' => Auth::id() ?? 1,
            ]);

            event(new ShipmentDelivered($shipment));

            if ($allDelivered) {
                event(new DeliveryCompleted($do));
            }
        });
    }

    /**
     * Mark delivery as failed.
     */
    public function failDelivery(Shipment $shipment, string $reason): void
    {
        DB::transaction(function () use ($shipment, $reason) {
            if ($shipment->status !== 'in_transit') {
                throw new \InvalidArgumentException('Only in transit shipments can be marked as failed.');
            }

            $shipment->update([
                'status' => 'failed_delivery',
            ]);

            DeliveryEvent::create([
                'delivery_order_id' => $shipment->delivery_order_id,
                'shipment_id' => $shipment->id,
                'event_type' => 'failed',
                'event_data' => [
                    'status' => 'failed_delivery',
                    'reason' => $reason,
                ],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_by' => Auth::id() ?? 1,
            ]);
        });
    }

    /**
     * Process shipment return. Re-injects inventory back to origin warehouse.
     */
    public function processReturn(Shipment $shipment, string $reason): void
    {
        DB::transaction(function () use ($shipment, $reason) {
            if (! in_array($shipment->status, ['in_transit', 'failed_delivery'])) {
                throw new \InvalidArgumentException('Only in transit or failed shipments can be returned.');
            }

            $shipment->update([
                'status' => 'returned',
            ]);

            $do = $shipment->deliveryOrder;
            $shipmentItems = $shipment->items()->orderBy('delivery_order_item_id')->get();

            foreach ($shipmentItems as $shItem) {
                $doItem = DeliveryOrderItem::where('id', $shItem->delivery_order_item_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Revert shipped status quantity
                $doItem->decrement('quantity_shipped', $shItem->quantity_shipped);

                // Find inventory item to restore stock in origin warehouse
                $invItem = InventoryItem::where('warehouse_id', $do->warehouse_id)
                    ->where('sku', $doItem->sku)
                    ->lockForUpdate()
                    ->first();

                if ($invItem) {
                    $this->inventoryTxService->adjustStock(
                        $do->warehouse,
                        $invItem,
                        (float) $shItem->quantity_shipped,
                        'adjustment_in',
                        'shipment',
                        $shipment->id,
                        (float) $invItem->avg_cost_price,
                        "Returned shipment cargo: {$shipment->reference_no}. Reason: {$reason}"
                    );
                }
            }

            // Recalculate DO status
            $allReturned = true;
            $doItems = DeliveryOrderItem::where('delivery_order_id', $do->id)->get();
            foreach ($doItems as $item) {
                if ($item->quantity_shipped > 0) {
                    $allReturned = false;
                    break;
                }
            }

            if ($allReturned) {
                $do->update(['status' => 'approved']); // Reset DO back to approved state so it can be shipped again
            } else {
                $do->update(['status' => 'partially_shipped']);
            }

            DeliveryEvent::create([
                'delivery_order_id' => $do->id,
                'shipment_id' => $shipment->id,
                'event_type' => 'returned',
                'event_data' => [
                    'status' => 'returned',
                    'reason' => $reason,
                ],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_by' => Auth::id() ?? 1,
            ]);
        });
    }
}
