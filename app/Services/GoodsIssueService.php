<?php

namespace App\Services;

use App\Enums\GoodsIssueStatus;
use App\Enums\InventoryTransactionType;
use App\Models\GoodsIssue;
use App\Models\GoodsIssueItem;
use App\Models\InventoryItem;
use App\Models\InventoryReservation;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsIssueService
{
    public function __construct(
        protected InventoryTransactionService $transactionService,
        protected ReservationService $reservationService
    ) {}

    /**
     * Create Goods Issue draft from Sales Order.
     */
    public function createFromSalesOrder(SalesOrder $so, Warehouse $wh): GoodsIssue
    {
        return DB::transaction(function () use ($so, $wh) {
            $referenceNo = 'GI-'.strtoupper(uniqid());

            $gi = GoodsIssue::create([
                'reference_no' => $referenceNo,
                'sales_order_id' => $so->id,
                'warehouse_id' => $wh->id,
                'issued_date' => now()->toDateString(),
                'status' => GoodsIssueStatus::Draft,
                'notes' => "Auto-generated from SO #{$so->reference_no}",
                'created_by' => Auth::id(),
            ]);

            foreach ($so->items as $index => $soItem) {
                // Determine how much is pending
                $totalIssued = (float) GoodsIssueItem::whereHas('goodsIssue', function ($q) {
                    $q->where('status', GoodsIssueStatus::Issued);
                })
                    ->where('sales_order_item_id', $soItem->id)
                    ->sum('quantity_issued');

                $pendingQty = max(0.00, (float) $soItem->quantity - $totalIssued);

                if ($pendingQty > 0) {
                    $sku = strtoupper(implode('-', array_filter(explode(' ', preg_replace('/[^a-zA-Z0-9 ]/', '', $soItem->description)))));
                    if (empty($sku)) {
                        $sku = 'ITEM-'.str_pad($soItem->id ?? rand(100, 999), 3, '0', STR_PAD_LEFT);
                    }

                    GoodsIssueItem::create([
                        'goods_issue_id' => $gi->id,
                        'sales_order_item_id' => $soItem->id,
                        'sku' => $sku,
                        'description' => $soItem->description,
                        'quantity_issued' => $pendingQty,
                        'unit' => $soItem->unit ?? 'pcs',
                        'sort_order' => $index,
                    ]);
                }
            }

            return $gi;
        });
    }

    /**
     * Post/Issue Goods Issue and decrease stock.
     */
    public function issue(GoodsIssue $gi, ?string $remark = null): void
    {
        DB::transaction(function () use ($gi, $remark) {
            if ($gi->status !== GoodsIssueStatus::Draft) {
                throw ValidationException::withMessages([
                    'status' => ['Only Draft Goods Issues can be posted.'],
                ]);
            }

            // Verify availability for all items before issue
            foreach ($gi->items as $item) {
                $invItem = InventoryItem::where('warehouse_id', $gi->warehouse_id)
                    ->where('sku', $item->sku)
                    ->lockForUpdate()
                    ->first();

                $available = $invItem ? (float) $invItem->quantity_current - (float) $invItem->quantity_reserved : 0.00;

                // If there's an active reservation for this SO, the reserved qty is allowed to be issued.
                $reservation = null;
                if ($gi->sales_order_id && $invItem) {
                    $reservation = InventoryReservation::where('sales_order_id', $gi->sales_order_id)
                        ->where('inventory_item_id', $invItem->id)
                        ->where('status', 'active')
                        ->first();
                }

                $allowedQty = $available;
                if ($reservation) {
                    $allowedQty += ((float) $reservation->quantity_reserved - (float) $reservation->quantity_released);
                }

                if ($allowedQty < (float) $item->quantity_issued) {
                    throw ValidationException::withMessages([
                        'stock' => ["Insufficient available stock for SKU: {$item->sku} in Warehouse: {$gi->warehouse->name}. Available: {$allowedQty}, Requested: {$item->quantity_issued}."],
                    ]);
                }
            }

            $gi->update([
                'status' => GoodsIssueStatus::Issued,
                'remark' => $remark,
                'updated_by' => Auth::id(),
            ]);

            foreach ($gi->items as $item) {
                $invItem = InventoryItem::where('warehouse_id', $gi->warehouse_id)
                    ->where('sku', $item->sku)
                    ->lockForUpdate()
                    ->first();

                // Release reservation first if it is mapped to a SO
                if ($gi->salesOrder && $invItem) {
                    $this->reservationService->releaseReservation($gi->salesOrder, $invItem, (float) $item->quantity_issued);
                }

                // Mutate stock (decrease)
                $this->transactionService->adjustStock(
                    $gi->warehouse,
                    $invItem,
                    -((float) $item->quantity_issued),
                    InventoryTransactionType::Issue->value,
                    GoodsIssue::class,
                    $gi->id,
                    (float) ($invItem?->avg_cost_price ?? 0.00),
                    $gi->notes
                );
            }
        }, 3);
    }
}
