<?php

namespace App\Services;

use App\Models\GoodsIssue;
use App\Models\GoodsReceipt;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InventoryQueryService
{
    /**
     * Get the total value of all physical inventory items.
     */
    public function getTotalInventoryValue(): float
    {
        return (float) InventoryItem::query()
            ->sum(DB::raw('quantity_current * avg_cost_price'));
    }

    /**
     * Get low stock inventory items.
     * Threshold: current quantity is 5 or below (for demo/default).
     */
    public function getLowStockItems(int $threshold = 5, int $limit = 10): Collection
    {
        return InventoryItem::with(['warehouse'])
            ->where('quantity_current', '<=', $threshold)
            ->orderBy('quantity_current', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get total count of pending Goods Receipts.
     */
    public function getPendingReceiptsCount(): int
    {
        return GoodsReceipt::where('status', 'draft')->count();
    }

    /**
     * Get total count of pending Goods Issues.
     */
    public function getPendingIssuesCount(): int
    {
        return GoodsIssue::where('status', 'draft')->count();
    }
}
