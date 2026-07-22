<?php

namespace App\Services;

use App\Models\P2pGoodsReceiptItem;
use App\Models\P2pInvoice;
use App\Models\SalesEventOutbox;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FiveWayMatchEngine
{
    /**
     * Executes the 5-way matching logic on a vendor invoice.
     */
    public function matchInvoice(int $invoiceId): P2pInvoice
    {
        return DB::transaction(function () use ($invoiceId) {
            $invoice = P2pInvoice::with(['items', 'purchaseOrder.items', 'vendor'])->findOrFail($invoiceId);

            if ($invoice->matching_status === 'PASSED') {
                throw new InvalidArgumentException('Invoice is already matched and passed.');
            }

            // 1. Duplicate Invoice Check
            $duplicateExists = P2pInvoice::where('vendor_id', $invoice->vendor_id)
                ->where('invoice_no', $invoice->invoice_no)
                ->where('id', '!=', $invoice->id)
                ->exists();

            if ($duplicateExists) {
                $invoice->matching_status = 'HOLD_DISCREPANCY';
                $invoice->hold_reason_code = 'DUPLICATE_INVOICE_DETECTED';
                $invoice->save();

                return $invoice;
            }

            $po = $invoice->purchaseOrder;
            if (! $po) {
                throw new InvalidArgumentException('Invoice must be linked to a valid purchase order.');
            }

            $hasDiscrepancy = false;
            $reasonCode = null;

            foreach ($invoice->items as $invItem) {
                $poItem = $po->items->firstWhere('purchase_order_item_id', $invItem->purchase_order_item_id)
                    ?? $po->items->firstWhere('id', $invItem->purchase_order_item_id);

                if (! $poItem) {
                    $hasDiscrepancy = true;
                    $reasonCode = 'UNMATCHED_PO_LINE';
                    break;
                }

                // Retrieve WMS Goods Receipt parameters
                $grItem = P2pGoodsReceiptItem::where('purchase_order_item_id', $poItem->id)->first();
                if (! $grItem) {
                    $hasDiscrepancy = true;
                    $reasonCode = 'UNRECEIVED_GOODS';
                    break;
                }

                // 2. PO Price vs Invoice Price Check (Tolerance 5%)
                $priceVariance = (float) $invItem->unit_price_invoiced - (float) $poItem->unit_price;
                $allowedPriceVariance = (float) $poItem->unit_price * 0.05;

                if ($priceVariance > $allowedPriceVariance) {
                    $hasDiscrepancy = true;
                    $reasonCode = 'PRICE_VARIANCE_EXCEEDED';
                    break;
                }

                // 3. PO Quantity vs Invoice Quantity Check
                if ((float) $invItem->quantity_invoiced > (float) $poItem->quantity_ordered) {
                    $hasDiscrepancy = true;
                    $reasonCode = 'QUANTITY_OVER_PO_LIMIT';
                    break;
                }

                // 4. GR Quantity vs Invoice Quantity Check
                if ((float) $invItem->quantity_invoiced > (float) $grItem->quantity_received) {
                    $hasDiscrepancy = true;
                    $reasonCode = 'QUANTITY_OVER_RECEIPT_LIMIT';
                    break;
                }

                // 5. QC Accepted Quantity vs Invoice Quantity Check (4-way/5-way QC constraint)
                if ((float) $invItem->quantity_invoiced > (float) $grItem->quantity_accepted) {
                    $hasDiscrepancy = true;
                    $reasonCode = 'QC_REJECTION_EXCEEDED';
                    break;
                }
            }

            if ($hasDiscrepancy) {
                $invoice->matching_status = 'HOLD_DISCREPANCY';
                $invoice->hold_reason_code = $reasonCode;
            } else {
                $invoice->matching_status = 'PASSED';
                $invoice->hold_reason_code = null;

                // Decoupled Financial Posting - post accrual creation event to outbox
                SalesEventOutbox::create([
                    'event_id' => 'evt-p2p-'.uniqid(),
                    'company_id' => $invoice->company_id,
                    'event_type' => 'InvoiceMatched',
                    'payload' => [
                        'invoice_id' => $invoice->id,
                        'invoice_no' => $invoice->invoice_no,
                        'amount' => (float) $invoice->amount_invoiced,
                        'vendor_id' => $invoice->vendor_id,
                        'purchase_order_id' => $invoice->purchase_order_id,
                        'branch_id' => $invoice->branch_id,
                        'schema_version' => 1,
                    ],
                    'correlation_id' => 'corr-p2p-'.uniqid(),
                    'causation_id' => 'caus-p2p-'.uniqid(),
                    'trace_id' => 'trace-p2p-'.uniqid(),
                    'idempotency_key' => 'idem-p2p-match-'.$invoice->id,
                    'is_dispatched' => false,
                ]);
            }

            $invoice->save();

            return $invoice;
        });
    }
}
