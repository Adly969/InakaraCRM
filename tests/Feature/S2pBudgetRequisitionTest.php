<?php

use App\Models\P2pBudget;
use App\Models\P2pRequisition;
use App\Models\P2pRequisitionItem;
use App\Services\RequisitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it submits requisition and locks budget reservation', function () {
    // 1. Create cost center budget limit
    $budget = P2pBudget::create([
        'company_id' => 1,
        'cost_center_code' => 'IT-DEPT',
        'fiscal_year' => now()->year,
        'allocated_amount' => 1000000.00,
        'reserved_amount' => 0.00,
        'committed_amount' => 0.00,
        'actual_spent_amount' => 0.00,
    ]);

    // 2. Create Requisition
    $requisition = P2pRequisition::create([
        'company_id' => 1,
        'branch_id' => 1,
        'requisition_no' => 'REQ-IT-001',
        'requester_id' => 1,
        'cost_center_code' => 'IT-DEPT',
        'type' => 'OPEX',
        'status' => 'DRAFT',
    ]);

    // Add items
    P2pRequisitionItem::create([
        'requisition_id' => $requisition->id,
        'sku' => 'LAPTOP-PRO',
        'quantity' => 2,
        'unit_price_estimate' => 300000.00, // Total = 600,000.00
    ]);

    $service = new RequisitionService;
    $updatedPR = $service->submitRequisition($requisition->id);

    expect($updatedPR->status)->toBe('PENDING')
        ->and((float) $updatedPR->total_amount)->toEqual(600000.00);

    // Verify budget is reserved
    $budget->refresh();
    expect((float) $budget->reserved_amount)->toEqual(600000.00);
});

test('it throws exception if requisition amount exceeds allocated budget', function () {
    P2pBudget::create([
        'company_id' => 1,
        'cost_center_code' => 'HR-DEPT',
        'fiscal_year' => now()->year,
        'allocated_amount' => 50000.00,
        'reserved_amount' => 0.00,
        'committed_amount' => 0.00,
        'actual_spent_amount' => 0.00,
    ]);

    $requisition = P2pRequisition::create([
        'company_id' => 1,
        'branch_id' => 1,
        'requisition_no' => 'REQ-HR-001',
        'requester_id' => 1,
        'cost_center_code' => 'HR-DEPT',
        'type' => 'OPEX',
        'status' => 'DRAFT',
    ]);

    P2pRequisitionItem::create([
        'requisition_id' => $requisition->id,
        'sku' => 'OFFICE-CHAIR',
        'quantity' => 10,
        'unit_price_estimate' => 10000.00, // Total = 100,000.00 (Exceeds budget of 50,000.00)
    ]);

    $service = new RequisitionService;

    expect(fn () => $service->submitRequisition($requisition->id))
        ->toThrow(InvalidArgumentException::class);
});
