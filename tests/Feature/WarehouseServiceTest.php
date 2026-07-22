<?php

use App\Enums\WarehouseStatus;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates a warehouse and manages default status', function () {
    $service = new WarehouseService;

    // Create a first warehouse marked as default
    $wh1 = $service->create([
        'code' => 'WH-001',
        'name' => 'Main Warehouse',
        'type' => 'central',
        'is_default' => true,
        'status' => WarehouseStatus::Active,
        'address' => 'Some address',
    ]);

    expect($wh1->is_default)->toBeTrue();
    $this->assertDatabaseHas('warehouses', ['id' => $wh1->id, 'is_default' => true]);

    // Create a second warehouse and mark as default
    $wh2 = $service->create([
        'code' => 'WH-002',
        'name' => 'Transit Warehouse',
        'type' => 'transit',
        'is_default' => true,
        'status' => WarehouseStatus::Active,
    ]);

    expect($wh2->is_default)->toBeTrue();
    expect($wh1->refresh()->is_default)->toBeFalse();

    $this->assertDatabaseHas('warehouses', ['id' => $wh2->id, 'is_default' => true]);
    $this->assertDatabaseHas('warehouses', ['id' => $wh1->id, 'is_default' => false]);
});
