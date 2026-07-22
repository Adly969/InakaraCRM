<?php

namespace App\Services;

use App\Enums\InventoryTransactionType;
use App\Enums\StockAdjustmentStatus;
use App\Enums\StockAdjustmentType;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService
{
    public function __construct(protected InventoryTransactionService $transactionService) {}

    /**
     * Create Stock Adjustment draft.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): StockAdjustment
    {
        return DB::transaction(function () use ($data) {
            $referenceNo = 'ADJ-'.strtoupper(uniqid());

            $adj = StockAdjustment::create([
                'reference_no' => $referenceNo,
                'warehouse_id' => $data['warehouse_id'],
                'adjustment_date' => $data['adjustment_date'] ?? now()->toDateString(),
                'status' => StockAdjustmentStatus::Draft,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($data['items'] as $index => $itemData) {
                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adj->id,
                    'inventory_item_id' => $itemData['inventory_item_id'],
                    'type' => $itemData['type'],
                    'quantity_adjusted' => $itemData['quantity_adjusted'],
                    'unit_cost' => $itemData['unit_cost'] ?? 0.00,
                    'sort_order' => $index,
                ]);
            }

            return $adj;
        });
    }

    /**
     * Approve Stock Adjustment and post mutations.
     */
    public function approve(StockAdjustment $adj, ?string $approvalNote = null): void
    {
        DB::transaction(function () use ($adj, $approvalNote) {
            if ($adj->status !== StockAdjustmentStatus::Draft) {
                throw ValidationException::withMessages([
                    'status' => ['Only Draft Stock Adjustments can be approved.'],
                ]);
            }

            $adj->update([
                'status' => StockAdjustmentStatus::Approved,
                'approval_note' => $approvalNote,
                'approved_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            foreach ($adj->items as $item) {
                $invItem = $item->inventoryItem;

                // Lock the record
                $invItem->lockForUpdate();

                $qtyChange = (float) $item->quantity_adjusted;
                $txType = InventoryTransactionType::AdjustmentIn->value;

                if ($item->type === StockAdjustmentType::Deduction) {
                    $qtyChange = -$qtyChange;
                    $txType = InventoryTransactionType::AdjustmentOut->value;
                }

                $this->transactionService->adjustStock(
                    $adj->warehouse,
                    $invItem,
                    $qtyChange,
                    $txType,
                    StockAdjustment::class,
                    $adj->id,
                    (float) $item->unit_cost,
                    $adj->notes
                );
            }
        }, 3);
    }

    /**
     * Reject Stock Adjustment.
     */
    public function reject(StockAdjustment $adj): void
    {
        DB::transaction(function () use ($adj) {
            if ($adj->status !== StockAdjustmentStatus::Draft) {
                throw ValidationException::withMessages([
                    'status' => ['Only Draft Stock Adjustments can be rejected.'],
                ]);
            }

            $adj->update([
                'status' => StockAdjustmentStatus::Rejected,
                'updated_by' => Auth::id(),
            ]);
        });
    }
}
