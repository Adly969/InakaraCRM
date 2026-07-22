<?php

namespace App\Services;

use App\Enums\GoodsReceiptStatus;
use App\Enums\InventoryTransactionType;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryItem;
use App\Models\ProductionOrder;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceiptService
{
    public function __construct(protected InventoryTransactionService $transactionService) {}

    /**
     * Create Goods Receipt draft from Production Order.
     */
    public function createFromProductionOrder(ProductionOrder $po, Warehouse $wh): GoodsReceipt
    {
        return DB::transaction(function () use ($po, $wh) {
            $referenceNo = 'GR-'.strtoupper(uniqid());

            $gr = GoodsReceipt::create([
                'reference_no' => $referenceNo,
                'production_order_id' => $po->id,
                'warehouse_id' => $wh->id,
                'received_date' => now()->toDateString(),
                'status' => GoodsReceiptStatus::Draft,
                'notes' => "Auto-generated from PO #{$po->reference_no}",
                'created_by' => Auth::id(),
            ]);

            foreach ($po->items as $index => $poItem) {
                // Determine how much is pending
                $totalReceived = (float) GoodsReceiptItem::whereHas('goodsReceipt', function ($q) {
                    $q->where('status', GoodsReceiptStatus::Received);
                })
                    ->where('production_order_item_id', $poItem->id)
                    ->sum('quantity_received');

                $pendingQty = max(0.00, (float) $poItem->quantity - $totalReceived);

                if ($pendingQty > 0) {
                    $sku = strtoupper(implode('-', array_filter(explode(' ', preg_replace('/[^a-zA-Z0-9 ]/', '', $poItem->description)))));
                    if (empty($sku)) {
                        $sku = 'ITEM-'.str_pad($poItem->id ?? rand(100, 999), 3, '0', STR_PAD_LEFT);
                    }

                    GoodsReceiptItem::create([
                        'goods_receipt_id' => $gr->id,
                        'production_order_item_id' => $poItem->id,
                        'sku' => $sku,
                        'description' => $poItem->description,
                        'quantity_received' => $pendingQty,
                        'unit' => $poItem->unit ?? 'pcs',
                        'unit_cost' => $poItem->unit_price ?? 0.00,
                        'sort_order' => $index,
                    ]);
                }
            }

            return $gr;
        });
    }

    /**
     * Post/Receive Goods Receipt and increase stock.
     */
    public function receive(GoodsReceipt $gr, ?string $remark = null): void
    {
        DB::transaction(function () use ($gr, $remark) {
            if ($gr->status !== GoodsReceiptStatus::Draft) {
                throw ValidationException::withMessages([
                    'status' => ['Only Draft Goods Receipts can be posted.'],
                ]);
            }

            $gr->update([
                'status' => GoodsReceiptStatus::Received,
                'remark' => $remark,
                'updated_by' => Auth::id(),
            ]);

            foreach ($gr->items as $item) {
                // Find or create InventoryItem in the target warehouse
                $invItem = InventoryItem::where('warehouse_id', $gr->warehouse_id)
                    ->where('sku', $item->sku)
                    ->lockForUpdate()
                    ->first();

                if (! $invItem) {
                    $invItem = InventoryItem::create([
                        'warehouse_id' => $gr->warehouse_id,
                        'sku' => $item->sku,
                        'name' => $item->description,
                        'description' => $item->description,
                        'quantity_current' => 0.00,
                        'quantity_reserved' => 0.00,
                        'unit' => $item->unit,
                        'avg_cost_price' => 0.00,
                        'created_by' => Auth::id(),
                    ]);
                }

                $this->transactionService->adjustStock(
                    $gr->warehouse,
                    $invItem,
                    (float) $item->quantity_received,
                    InventoryTransactionType::Receipt->value,
                    GoodsReceipt::class,
                    $gr->id,
                    (float) $item->unit_cost,
                    $gr->notes
                );
            }
        }, 3);
    }
}
