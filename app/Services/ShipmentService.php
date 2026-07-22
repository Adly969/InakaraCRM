<?php

namespace App\Services;

use App\Events\ShipmentCreated;
use App\Events\ShipmentDispatched;
use App\Models\DeliveryEvent;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;

class ShipmentService
{
    public function __construct(
        protected DeliveryNumberGenerator $numberGenerator
    ) {}

    /**
     * Create a new Shipment dispatch.
     */
    public function createShipment(DeliveryOrder $do, array $data): Shipment
    {
        return DB::transaction(function () use ($do, $data) {
            if ($do->status === 'draft' || $do->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'delivery_order_id' => "Cannot create shipment for a Delivery Order in {$do->status} status.",
                ]);
            }

            // Sort item lines ascending by delivery_order_item_id to prevent circular deadlocks
            $items = $data['items'];
            usort($items, function ($a, $b) {
                return $a['delivery_order_item_id'] <=> $b['delivery_order_item_id'];
            });

            $shipment = Shipment::create([
                'delivery_order_id' => $do->id,
                'reference_no' => $this->numberGenerator->generateShipmentNumber(),
                'carrier_id' => $data['carrier_id'] ?? null,
                'driver_id' => $data['driver_id'] ?? null,
                'courier_type' => $data['courier_type'],
                'tracking_number' => $data['tracking_number'] ?? null,
                'status' => 'pending_dispatch',
                'estimated_cost' => $data['estimated_cost'] ?? 0.00,
                'actual_cost' => 0.00,
                'currency' => $data['currency'] ?? 'IDR',
                'exchange_rate' => $data['exchange_rate'] ?? 1.000000,
                'estimated_delivery_date' => $data['estimated_delivery_date'] ?? null,
                'created_by' => Auth::id() ?? 1,
            ]);

            foreach ($items as $item) {
                $doItem = DeliveryOrderItem::where('id', $item['delivery_order_item_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($doItem->delivery_order_id !== $do->id) {
                    throw ValidationException::withMessages([
                        'items' => "Invalid Delivery Order Item ID: {$item['delivery_order_item_id']}.",
                    ]);
                }

                $remaining = $doItem->quantity_requested - $doItem->quantity_shipped;
                if ($item['quantity_shipped'] > $remaining) {
                    throw ValidationException::withMessages([
                        'items' => "Requested shipment quantity for {$doItem->sku} ({$item['quantity_shipped']}) exceeds remaining requested quantity ({$remaining}).",
                    ]);
                }

                ShipmentItem::create([
                    'shipment_id' => $shipment->id,
                    'delivery_order_item_id' => $doItem->id,
                    'quantity_shipped' => $item['quantity_shipped'],
                ]);

                // Update cumulative shipped qty
                $doItem->increment('quantity_shipped', $item['quantity_shipped']);
            }

            // Recalculate DO status
            $allFullyShipped = true;
            $doItems = DeliveryOrderItem::where('delivery_order_id', $do->id)->get();
            foreach ($doItems as $item) {
                if ($item->quantity_shipped < $item->quantity_requested) {
                    $allFullyShipped = false;
                    break;
                }
            }

            $newDoStatus = $allFullyShipped ? 'shipped' : 'partially_shipped';
            $do->update(['status' => $newDoStatus]);

            // Log event
            DeliveryEvent::create([
                'delivery_order_id' => $do->id,
                'shipment_id' => $shipment->id,
                'event_type' => 'created',
                'event_data' => [
                    'shipment_reference_no' => $shipment->reference_no,
                    'status' => 'pending_dispatch',
                ],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_by' => Auth::id() ?? 1,
            ]);

            event(new ShipmentCreated($shipment));

            return $shipment;
        }, 3);
    }

    /**
     * Dispatch a shipment to in_transit.
     */
    public function dispatch(Shipment $shipment): void
    {
        DB::transaction(function () use ($shipment) {
            if ($shipment->status !== 'pending_dispatch') {
                throw new \InvalidArgumentException('Only pending_dispatch shipments can be dispatched.');
            }

            $shipment->update([
                'status' => 'in_transit',
            ]);

            DeliveryEvent::create([
                'delivery_order_id' => $shipment->delivery_order_id,
                'shipment_id' => $shipment->id,
                'event_type' => 'dispatched',
                'event_data' => [
                    'status' => 'in_transit',
                ],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_by' => Auth::id() ?? 1,
            ]);

            event(new ShipmentDispatched($shipment));
        });
    }
}
