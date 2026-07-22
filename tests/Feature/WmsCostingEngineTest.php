<?php

use App\Models\WmsCostLayer;
use App\Services\CostingEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it records inbound costing layers', function () {
    $engine = new CostingEngine;
    $layer = $engine->recordInboundLayer(1, 'TEST-SKU-1', 10.00, 150.00);

    expect($layer)->toBeInstanceOf(WmsCostLayer::class)
        ->and($layer->company_id)->toBe(1)
        ->and($layer->sku)->toBe('TEST-SKU-1')
        ->and((float) $layer->quantity_initial)->toBe(10.00)
        ->and((float) $layer->quantity_remaining)->toBe(10.00)
        ->and((float) $layer->unit_cost)->toBe(150.00);
});

test('it issues stock using FIFO layering queue logic', function () {
    $engine = new CostingEngine;

    // Layer 1: 10 units at $10.00 each
    $engine->recordInboundLayer(1, 'FIFO-SKU', 10.00, 10.00);

    // Layer 2: 5 units at $15.00 each
    $engine->recordInboundLayer(1, 'FIFO-SKU', 5.00, 15.00);

    // Issue 12 units. Cost should be (10 * $10.00) + (2 * $15.00) = $100.00 + $30.00 = $130.00
    $totalCost = $engine->issueFifo(1, 'FIFO-SKU', 12.00);

    expect($totalCost)->toEqual(130.00);

    // Verify remaining layers quantity_remaining
    $layers = WmsCostLayer::where('sku', 'FIFO-SKU')->orderBy('receipt_date', 'asc')->get();
    expect((float) $layers[0]->quantity_remaining)->toEqual(0.00)
        ->and((float) $layers[1]->quantity_remaining)->toEqual(3.00);
});

test('it throws exception if issuing more than available layers quantity', function () {
    $engine = new CostingEngine;
    $engine->recordInboundLayer(1, 'FIFO-SKU-LIMIT', 5.00, 10.00);

    expect(fn () => $engine->issueFifo(1, 'FIFO-SKU-LIMIT', 6.00))
        ->toThrow(InvalidArgumentException::class);
});

test('it calculates moving average costing changes on receipt', function () {
    $engine = new CostingEngine;

    // Current: 10 units at $100.00 avg cost
    // Inbound: 5 units at $130.00 unit cost
    // New Cost = ((10 * 100) + (5 * 130)) / 15 = (1000 + 650) / 15 = 1650 / 15 = 110.00
    $newAvgCost = $engine->calculateMovingAverage(10.00, 100.00, 5.00, 130.00);

    expect($newAvgCost)->toEqual(110.00);
});
