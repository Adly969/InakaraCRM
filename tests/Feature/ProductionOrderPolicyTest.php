<?php

use App\Enums\UserRole;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
    $this->owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $this->admin = User::factory()->create()->assignRole(UserRole::Admin->value);
    $this->manager = User::factory()->create()->assignRole(UserRole::Manager->value);
    $this->cs = User::factory()->create()->assignRole(UserRole::CustomerService->value);
    $this->produksi = User::factory()->create()->assignRole(UserRole::Produksi->value);
    $this->sales = User::factory()->create()->assignRole(UserRole::Sales->value);
});

test('owners and admins have full access', function () {
    $po = ProductionOrder::factory()->create();

    $this->actingAs($this->owner);
    expect(Gate::allows('view', $po))->toBeTrue()
        ->and(Gate::allows('update', $po))->toBeTrue()
        ->and(Gate::allows('delete', $po))->toBeTrue()
        ->and(Gate::allows('cancel', $po))->toBeTrue();

    $this->actingAs($this->admin);
    expect(Gate::allows('view', $po))->toBeTrue()
        ->and(Gate::allows('update', $po))->toBeTrue()
        ->and(Gate::allows('delete', $po))->toBeTrue()
        ->and(Gate::allows('cancel', $po))->toBeTrue();
});

test('managers can manage but not delete', function () {
    $po = ProductionOrder::factory()->create();

    $this->actingAs($this->manager);
    expect(Gate::allows('view', $po))->toBeTrue()
        ->and(Gate::allows('update', $po))->toBeTrue()
        ->and(Gate::allows('cancel', $po))->toBeTrue()
        ->and(Gate::allows('delete', $po))->toBeFalse();
});

test('produksi can only view and manage assigned production orders', function () {
    $assignedPO = ProductionOrder::factory()->create(['assigned_to' => $this->produksi->id]);
    $unassignedPO = ProductionOrder::factory()->create(['assigned_to' => User::factory()->create()->id]);

    $this->actingAs($this->produksi);

    // Assigned PO
    expect(Gate::allows('view', $assignedPO))->toBeTrue()
        ->and(Gate::allows('update', $assignedPO))->toBeTrue()
        ->and(Gate::allows('cancel', $assignedPO))->toBeTrue();

    // Unassigned PO
    expect(Gate::allows('view', $unassignedPO))->toBeFalse()
        ->and(Gate::allows('update', $unassignedPO))->toBeFalse()
        ->and(Gate::allows('cancel', $unassignedPO))->toBeFalse();
});

test('customer service has view-only access', function () {
    $po = ProductionOrder::factory()->create();

    $this->actingAs($this->cs);
    expect(Gate::allows('view', $po))->toBeTrue()
        ->and(Gate::allows('update', $po))->toBeFalse()
        ->and(Gate::allows('delete', $po))->toBeFalse()
        ->and(Gate::allows('cancel', $po))->toBeFalse();
});
