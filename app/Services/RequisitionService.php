<?php

namespace App\Services;

use App\Models\P2pBudget;
use App\Models\P2pRequisition;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RequisitionService
{
    /**
     * Submits a purchase requisition and locks budget reservation balance.
     */
    public function submitRequisition(int $requisitionId): P2pRequisition
    {
        return DB::transaction(function () use ($requisitionId) {
            $requisition = P2pRequisition::with('items')->findOrFail($requisitionId);

            if ($requisition->status !== 'DRAFT') {
                throw new InvalidArgumentException('Only draft requisitions can be submitted.');
            }

            $year = now()->year;
            $costCenter = $requisition->cost_center_code;

            // Enforce pessimistic lock on cost center budget limit rows to prevent race condition overruns
            $budget = P2pBudget::where('company_id', $requisition->company_id)
                ->where('cost_center_code', $costCenter)
                ->where('fiscal_year', $year)
                ->lockForUpdate()
                ->first();

            if (! $budget) {
                throw new InvalidArgumentException("No budget allocation found for Cost Center {$costCenter} in fiscal year {$year}.");
            }

            $totalAmount = (float) $requisition->items->sum(function ($item) {
                return (float) $item->quantity * (float) $item->unit_price_estimate;
            });

            $available = (float) $budget->allocated_amount - ((float) $budget->reserved_amount + (float) $budget->committed_amount + (float) $budget->actual_spent_amount);

            if ($available < $totalAmount) {
                throw new InvalidArgumentException("Insufficient budget. Available: {$available}, Requested: {$totalAmount}.");
            }

            // Reserve budget balance
            $budget->reserved_amount = (float) $budget->reserved_amount + $totalAmount;
            $budget->save();

            $requisition->total_amount = $totalAmount;
            $requisition->status = 'PENDING';
            $requisition->save();

            return $requisition;
        });
    }

    /**
     * Approves the requisition and updates budget state from reserved to committed.
     */
    public function approveRequisition(int $requisitionId): P2pRequisition
    {
        return DB::transaction(function () use ($requisitionId) {
            $requisition = P2pRequisition::findOrFail($requisitionId);

            if ($requisition->status !== 'PENDING') {
                throw new InvalidArgumentException('Only pending requisitions can be approved.');
            }

            $year = now()->year;
            $costCenter = $requisition->cost_center_code;

            $budget = P2pBudget::where('company_id', $requisition->company_id)
                ->where('cost_center_code', $costCenter)
                ->where('fiscal_year', $year)
                ->lockForUpdate()
                ->first();

            if ($budget) {
                // Convert reservation to commitment
                $budget->reserved_amount = max(0.00, (float) $budget->reserved_amount - (float) $requisition->total_amount);
                $budget->committed_amount = (float) $budget->committed_amount + (float) $requisition->total_amount;
                $budget->save();
            }

            $requisition->status = 'APPROVED';
            $requisition->save();

            return $requisition;
        });
    }
}
