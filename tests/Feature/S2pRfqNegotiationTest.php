<?php

use App\Models\P2pBid;
use App\Models\P2pRfq;
use App\Models\P2pVendor;
use App\Services\RfqNegotiationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it evaluates bids and awards tender to the highest weighted score vendor', function () {
    // 1. Create RFQ
    $rfq = P2pRfq::create([
        'company_id' => 1,
        'branch_id' => 1,
        'rfq_no' => 'RFQ-2026-001',
        'status' => 'OPEN',
        'close_date' => now()->addDays(5),
    ]);

    // 2. Create Vendors
    $vendorA = P2pVendor::create([
        'company_id' => 1,
        'branch_id' => 1,
        'code' => 'VEND-A',
        'name' => 'Supplier A',
        'category' => 'GENERAL',
        'payment_terms_code' => 'NET30',
    ]);

    $vendorB = P2pVendor::create([
        'company_id' => 1,
        'branch_id' => 1,
        'code' => 'VEND-B',
        'name' => 'Supplier B',
        'category' => 'GENERAL',
        'payment_terms_code' => 'NET30',
    ]);

    // Bid A: Tech score = 90, Quote = 1500 (lower tech, lower quote)
    P2pBid::create([
        'rfq_id' => $rfq->id,
        'vendor_id' => $vendorA->id,
        'bid_no' => 'BID-A',
        'technical_score' => 90.00,
        'commercial_quote' => 1500.00,
    ]);

    // Bid B: Tech score = 98, Quote = 2000 (higher tech, higher quote)
    P2pBid::create([
        'rfq_id' => $rfq->id,
        'vendor_id' => $vendorB->id,
        'bid_no' => 'BID-B',
        'technical_score' => 98.00,
        'commercial_quote' => 2000.00,
    ]);

    $engine = new RfqNegotiationEngine;

    // Evaluate: 60% Technical, 40% Commercial
    // Lowest quote is 1500
    // Bid A: tech rating = 90 * 0.60 = 54; comm rating = (1500/1500 * 100) * 0.40 = 40; total = 94.00
    // Bid B: tech rating = 98 * 0.60 = 58.8; comm rating = (1500/2000 * 100) * 0.40 = 30; total = 88.80
    $winningBid = $engine->evaluateBids($rfq->id, 0.6, 0.4);

    expect($winningBid)->not->toBeNull()
        ->and($winningBid->bid_no)->toBe('BID-A');
});
