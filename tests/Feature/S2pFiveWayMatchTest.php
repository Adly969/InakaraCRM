<?php

use App\Models\P2pGoodsReceipt;
use App\Models\P2pGoodsReceiptItem;
use App\Models\P2pInvoice;
use App\Models\P2pInvoiceItem;
use App\Models\P2pPurchaseOrder;
use App\Models\P2pPurchaseOrderItem;
use App\Models\P2pVendor;
use App\Models\SalesEventOutbox;
use App\Services\FiveWayMatchEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it passes 5-way matching if all prices and quantities match within tolerance limits', function () {
    $vendor = P2pVendor::create([
        'company_id' => 1,
        'branch_id' => 1,
        'code' => 'VEND-MATCH',
        'name' => 'Matching Supplier',
        'category' => 'GENERAL',
        'payment_terms_code' => 'NET30',
    ]);

    $po = P2pPurchaseOrder::create([
        'company_id' => 1,
        'branch_id' => 1,
        'vendor_id' => $vendor->id,
        'po_no' => 'PO-MATCH-01',
        'total_amount' => 1000.00,
    ]);

    $poItem = P2pPurchaseOrderItem::create([
        'purchase_order_id' => $po->id,
        'sku' => 'MATCH-SKU',
        'quantity_ordered' => 10.00,
        'unit_price' => 100.00,
    ]);

    $gr = P2pGoodsReceipt::create([
        'company_id' => 1,
        'branch_id' => 1,
        'purchase_order_id' => $po->id,
        'receipt_no' => 'GR-MATCH-01',
        'received_at' => now(),
    ]);

    P2pGoodsReceiptItem::create([
        'goods_receipt_id' => $gr->id,
        'purchase_order_item_id' => $poItem->id,
        'sku' => 'MATCH-SKU',
        'quantity_received' => 10.00,
        'quantity_accepted' => 10.00,
        'quantity_rejected' => 0.00,
        'status' => 'QC_COMPLETED',
    ]);

    $invoice = P2pInvoice::create([
        'company_id' => 1,
        'branch_id' => 1,
        'invoice_no' => 'INV-MATCH-01',
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'amount_invoiced' => 1000.00,
    ]);

    P2pInvoiceItem::create([
        'invoice_id' => $invoice->id,
        'purchase_order_item_id' => $poItem->id,
        'quantity_invoiced' => 10.00,
        'unit_price_invoiced' => 100.00,
    ]);

    $engine = new FiveWayMatchEngine;
    $result = $engine->matchInvoice($invoice->id);

    expect($result->matching_status)->toBe('PASSED')
        ->and($result->hold_reason_code)->toBeNull();

    // Verify outbox entry is generated for general ledger posting
    expect(SalesEventOutbox::count())->toBe(1);
});

test('it places discrepancy hold if price variance exceeds tolerance', function () {
    $vendor = P2pVendor::create([
        'company_id' => 1,
        'branch_id' => 1,
        'code' => 'VEND-MATCH-2',
        'name' => 'Matching Supplier 2',
        'category' => 'GENERAL',
        'payment_terms_code' => 'NET30',
    ]);

    $po = P2pPurchaseOrder::create([
        'company_id' => 1,
        'branch_id' => 1,
        'vendor_id' => $vendor->id,
        'po_no' => 'PO-MATCH-02',
        'total_amount' => 100.00,
    ]);

    $poItem = P2pPurchaseOrderItem::create([
        'purchase_order_id' => $po->id,
        'sku' => 'MATCH-SKU-2',
        'quantity_ordered' => 1.00,
        'unit_price' => 100.00,
    ]);

    $gr = P2pGoodsReceipt::create([
        'company_id' => 1,
        'branch_id' => 1,
        'purchase_order_id' => $po->id,
        'receipt_no' => 'GR-MATCH-02',
        'received_at' => now(),
    ]);

    P2pGoodsReceiptItem::create([
        'goods_receipt_id' => $gr->id,
        'purchase_order_item_id' => $poItem->id,
        'sku' => 'MATCH-SKU-2',
        'quantity_received' => 1.00,
        'quantity_accepted' => 1.00,
        'quantity_rejected' => 0.00,
        'status' => 'QC_COMPLETED',
    ]);

    $invoice = P2pInvoice::create([
        'company_id' => 1,
        'branch_id' => 1,
        'invoice_no' => 'INV-MATCH-02',
        'vendor_id' => $vendor->id,
        'purchase_order_id' => $po->id,
        'amount_invoiced' => 120.00,
    ]);

    // Unit price is 120.00 (Exceeds PO price 100.00 by 20%, which is > 5% allowed tolerance)
    P2pInvoiceItem::create([
        'invoice_id' => $invoice->id,
        'purchase_order_item_id' => $poItem->id,
        'quantity_invoiced' => 1.00,
        'unit_price_invoiced' => 120.00,
    ]);

    $engine = new FiveWayMatchEngine;
    $result = $engine->matchInvoice($invoice->id);

    expect($result->matching_status)->toBe('HOLD_DISCREPANCY')
        ->and($result->hold_reason_code)->toBe('PRICE_VARIANCE_EXCEEDED');
});
