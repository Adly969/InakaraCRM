<?php

namespace App\Services;

use App\Models\P2pContract;
use App\Models\P2pPurchaseOrder;
use App\Models\P2pVendor;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseOrderService
{
    /**
     * Creates a purchase order and updates matching supplier contracts.
     */
    public function createPurchaseOrder(array $data): P2pPurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $vendor = P2pVendor::findOrFail($data['vendor_id']);

            // Vendor eligibility rule check
            if ($vendor->qualification_status === 'BLACKLISTED') {
                throw new InvalidArgumentException("Cannot issue Purchase Order to blacklisted Supplier [{$vendor->name}].");
            }

            $contract = null;
            $totalAmount = (float) ($data['total_amount'] ?? 0.00);

            if (! empty($data['contract_id'])) {
                $contract = P2pContract::lockForUpdate()->findOrFail($data['contract_id']);

                if ($contract->status !== 'ACTIVE') {
                    throw new InvalidArgumentException("Associated contract [{$contract->contract_no}] is not active.");
                }

                $remaining = (float) $contract->total_value_limit - (float) $contract->released_value;
                if ($remaining < $totalAmount) {
                    throw new InvalidArgumentException("Purchase order total [{$totalAmount}] exceeds remaining contract limit [{$remaining}].");
                }

                // Increment released value on parent contract
                $contract->released_value = (float) $contract->released_value + $totalAmount;
                $contract->save();
            }

            $po = P2pPurchaseOrder::create([
                'company_id' => $data['company_id'],
                'branch_id' => $data['branch_id'],
                'vendor_id' => $vendor->id,
                'contract_id' => $contract ? $contract->id : null,
                'po_no' => $data['po_no'],
                'type' => $data['type'] ?? 'STANDARD',
                'status' => 'DRAFT',
                'currency_code' => $data['currency_code'] ?? 'IDR',
                'exchange_rate' => $data['exchange_rate'] ?? 1.0000,
                'total_amount' => $totalAmount,
            ]);

            return $po;
        });
    }
}
