<?php

use App\Models\P2pContract;
use App\Models\P2pVendor;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates purchase order and increments released value on active contract', function () {
    $vendor = P2pVendor::create([
        'company_id' => 1,
        'branch_id' => 1,
        'code' => 'VEND-COMP',
        'name' => 'Compliant Supplier',
        'category' => 'CRITICAL',
        'qualification_status' => 'QUALIFIED',
        'payment_terms_code' => 'NET30',
    ]);

    $contract = P2pContract::create([
        'company_id' => 1,
        'branch_id' => 1,
        'vendor_id' => $vendor->id,
        'contract_no' => 'CON-100',
        'type' => 'FRAMEWORK',
        'status' => 'ACTIVE',
        'start_date' => now()->subDay(),
        'end_date' => now()->addYear(),
        'total_value_limit' => 5000000.00,
        'released_value' => 0.00,
    ]);

    $service = new PurchaseOrderService;

    $po = $service->createPurchaseOrder([
        'company_id' => 1,
        'branch_id' => 1,
        'vendor_id' => $vendor->id,
        'contract_id' => $contract->id,
        'po_no' => 'PO-2026-X',
        'total_amount' => 1200000.00,
    ]);

    expect($po->status)->toBe('DRAFT')
        ->and((float) $po->total_amount)->toEqual(1200000.00);

    // Verify contract released value updated
    $contract->refresh();
    expect((float) $contract->released_value)->toEqual(1200000.00);
});

test('it blocks purchase order creation if vendor is blacklisted', function () {
    $vendor = P2pVendor::create([
        'company_id' => 1,
        'branch_id' => 1,
        'code' => 'VEND-BAD',
        'name' => 'Blacklisted Supplier',
        'category' => 'GENERAL',
        'qualification_status' => 'BLACKLISTED',
        'payment_terms_code' => 'NET30',
    ]);

    $service = new PurchaseOrderService;

    expect(fn () => $service->createPurchaseOrder([
        'company_id' => 1,
        'branch_id' => 1,
        'vendor_id' => $vendor->id,
        'po_no' => 'PO-2026-FAIL',
        'total_amount' => 1000.00,
    ]))->toThrow(InvalidArgumentException::class);
});
