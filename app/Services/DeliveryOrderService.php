<?php

namespace App\Services;

use App\Events\DeliveryApproved;
use App\Events\DeliveryCancelled;
use App\Models\Customer;
use App\Models\DeliveryEvent;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class DeliveryOrderService
{
    public function __construct(
        protected DeliveryNumberGenerator $numberGenerator,
        protected DeliveryValidationService $validationService
    ) {}

    /**
     * Create a new Delivery Order.
     */
    public function create(array $data): DeliveryOrder
    {
        return DB::transaction(function () use ($data) {
            $salesOrder = SalesOrder::findOrFail($data['sales_order_id']);
            $customer = Customer::findOrFail($data['customer_id']);

            // Validate outstanding quantities
            $this->validationService->validateOutstandingQuantity($salesOrder, $data['items']);

            // Snapshot addresses
            $shippingAddress = $data['shipping_address'] ?? $customer->shipping_address ?? '';
            $billingAddress = $data['billing_address'] ?? $customer->billing_address ?? '';

            $shippingSnapshot = [
                'name' => $customer->name,
                'address' => $shippingAddress,
            ];

            $billingSnapshot = [
                'name' => $customer->name,
                'address' => $billingAddress,
            ];

            $do = DeliveryOrder::create([
                'reference_no' => $this->numberGenerator->generateDoNumber(),
                'sales_order_id' => $salesOrder->id,
                'warehouse_id' => $data['warehouse_id'],
                'customer_id' => $customer->id,
                'company_id' => $data['company_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'status' => 'draft',
                'shipping_address_snapshot' => $shippingSnapshot,
                'billing_address_snapshot' => $billingSnapshot,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id() ?? 1,
            ]);

            foreach ($data['items'] as $item) {
                $soItem = SalesOrderItem::findOrFail($item['sales_order_item_id']);

                // Snapshot item details (dimensions/weight stubs)
                $itemSpecs = [
                    'weight' => 0.00,
                    'volume' => 0.00,
                    'dimensions' => 'N/A',
                ];

                $sku = strtoupper(implode('-', array_filter(explode(' ', preg_replace('/[^a-zA-Z0-9 ]/', '', $soItem->description)))));
                if (empty($sku)) {
                    $sku = 'ITEM-'.str_pad($soItem->id ?? rand(100, 999), 3, '0', STR_PAD_LEFT);
                }

                DeliveryOrderItem::create([
                    'delivery_order_id' => $do->id,
                    'sales_order_item_id' => $soItem->id,
                    'sku' => $sku,
                    'description' => $soItem->description,
                    'quantity_requested' => $item['quantity_requested'],
                    'quantity_shipped' => 0.00,
                    'quantity_delivered' => 0.00,
                    'unit' => $soItem->unit ?? 'pcs',
                    'item_specifications_snapshot' => $itemSpecs,
                    'sort_order' => $item['sort_order'] ?? 0,
                ]);
            }

            // Log telemetry event
            DeliveryEvent::create([
                'delivery_order_id' => $do->id,
                'event_type' => 'created',
                'event_data' => [
                    'reference_no' => $do->reference_no,
                    'status' => 'draft',
                ],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_by' => Auth::id() ?? 1,
            ]);

            return $do;
        }, 3);
    }

    /**
     * Approve a Delivery Order.
     */
    public function approve(DeliveryOrder $do): void
    {
        DB::transaction(function () use ($do) {
            if ($do->status !== 'draft') {
                throw new \InvalidArgumentException('Only draft Delivery Orders can be approved.');
            }

            $do->update([
                'status' => 'approved',
                'approved_by' => Auth::id() ?? 1,
                'approved_at' => now(),
            ]);

            DeliveryEvent::create([
                'delivery_order_id' => $do->id,
                'event_type' => 'approved',
                'event_data' => [
                    'status' => 'approved',
                    'approved_by' => Auth::id() ?? 1,
                ],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_by' => Auth::id() ?? 1,
            ]);

            event(new DeliveryApproved($do));
        });
    }

    /**
     * Cancel a Delivery Order.
     */
    public function cancel(DeliveryOrder $do, ?string $reason = null): void
    {
        DB::transaction(function () use ($do, $reason) {
            if (in_array($do->status, ['delivered', 'cancelled'])) {
                throw new \InvalidArgumentException("Cannot cancel a {$do->status} Delivery Order.");
            }

            $do->update([
                'status' => 'cancelled',
            ]);

            DeliveryEvent::create([
                'delivery_order_id' => $do->id,
                'event_type' => 'cancelled',
                'event_data' => [
                    'status' => 'cancelled',
                    'reason' => $reason,
                ],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_by' => Auth::id() ?? 1,
            ]);

            event(new DeliveryCancelled($do, $reason));
        });
    }
}
