<?php

use App\Models\WmsLocation;
use App\Models\WmsTask;
use App\Models\WmsWarehouse;
use App\Services\PutawayStrategy;
use App\Services\WavePickingEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it resolves best target bin using putaway capacity logic', function () {
    $wh = WmsWarehouse::create([
        'company_id' => 1,
        'branch_id' => 1,
        'code' => 'WH-PUT',
        'name' => 'Putaway Warehouse',
        'type' => 'central',
    ]);

    // Bin A: Weight limit 100, current 90 (10 available)
    $binA = WmsLocation::create([
        'warehouse_id' => $wh->id,
        'type' => 'BIN',
        'code' => 'BIN-A',
        'max_weight' => 100.00,
        'current_weight' => 90.00,
        'max_volume' => 50.00,
        'current_volume' => 10.00,
    ]);

    // Bin B: Weight limit 100, current 50 (50 available)
    $binB = WmsLocation::create([
        'warehouse_id' => $wh->id,
        'type' => 'BIN',
        'code' => 'BIN-B',
        'max_weight' => 100.00,
        'current_weight' => 50.00,
        'max_volume' => 50.00,
        'current_volume' => 10.00,
    ]);

    $strategy = new PutawayStrategy;

    // Inbound: item weight 30, volume 5
    $targetBin = $strategy->resolveTargetBin($wh->id, 30.00, 5.00);

    expect($targetBin->id)->toBe($binB->id);
});

test('it groups tasks into picking waves', function () {
    $engine = new WavePickingEngine;

    $task1 = new WmsTask(['sku' => 'SKU-A']);
    $task2 = new WmsTask(['sku' => 'SKU-A']);
    $task3 = new WmsTask(['sku' => 'SKU-B']);

    $tasks = collect([$task1, $task2, $task3]);

    $waves = $engine->generateWave($tasks);

    expect($waves->count())->toBe(2)
        ->and($waves->has('SKU-A-UNKNOWN'))->toBeTrue()
        ->and($waves->get('SKU-A-UNKNOWN')->count())->toBe(2);
});
