<?php

use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
});

test('viewAny allows users with view-leads permission', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);
    $finance = User::factory()->create()->assignRole(UserRole::Finance->value);

    expect(Gate::forUser($owner)->allows('viewAny', Lead::class))->toBeTrue()
        ->and(Gate::forUser($sales)->allows('viewAny', Lead::class))->toBeTrue()
        ->and(Gate::forUser($finance)->allows('viewAny', Lead::class))->toBeFalse();
});

test('view allows owner, admin, manager to view all, sales to view only assigned', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $manager = User::factory()->create()->assignRole(UserRole::Manager->value);
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);
    $salesOther = User::factory()->create()->assignRole(UserRole::Sales->value);

    $leadAssignedToSales = Lead::factory()->create(['assigned_to' => $sales->id]);
    $leadUnassigned = Lead::factory()->create();

    // Owner can view all
    expect(Gate::forUser($owner)->allows('view', $leadAssignedToSales))->toBeTrue()
        ->and(Gate::forUser($owner)->allows('view', $leadUnassigned))->toBeTrue();

    // Manager can view all
    expect(Gate::forUser($manager)->allows('view', $leadAssignedToSales))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('view', $leadUnassigned))->toBeTrue();

    // Sales can only view assigned to them
    expect(Gate::forUser($sales)->allows('view', $leadAssignedToSales))->toBeTrue()
        ->and(Gate::forUser($sales)->allows('view', $leadUnassigned))->toBeFalse();

    // Other sales cannot view assigned to sales
    expect(Gate::forUser($salesOther)->allows('view', $leadAssignedToSales))->toBeFalse();
});

test('create allows user with create-leads permission', function () {
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);
    $finance = User::factory()->create()->assignRole(UserRole::Finance->value);

    expect(Gate::forUser($sales)->allows('create', Lead::class))->toBeTrue()
        ->and(Gate::forUser($finance)->allows('create', Lead::class))->toBeFalse();
});

test('update allows owner, admin, manager to edit all, sales to edit only assigned', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $manager = User::factory()->create()->assignRole(UserRole::Manager->value);
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);
    $salesOther = User::factory()->create()->assignRole(UserRole::Sales->value);

    $leadAssignedToSales = Lead::factory()->create(['assigned_to' => $sales->id]);
    $leadUnassigned = Lead::factory()->create();

    // Owner can edit all
    expect(Gate::forUser($owner)->allows('update', $leadAssignedToSales))->toBeTrue()
        ->and(Gate::forUser($owner)->allows('update', $leadUnassigned))->toBeTrue();

    // Manager can edit all
    expect(Gate::forUser($manager)->allows('update', $leadAssignedToSales))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('update', $leadUnassigned))->toBeTrue();

    // Sales can only edit assigned to them
    expect(Gate::forUser($sales)->allows('update', $leadAssignedToSales))->toBeTrue()
        ->and(Gate::forUser($sales)->allows('update', $leadUnassigned))->toBeFalse();

    // Other sales cannot edit assigned to sales
    expect(Gate::forUser($salesOther)->allows('update', $leadAssignedToSales))->toBeFalse();
});

test('delete is restricted to owner and admin roles via permission', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $admin = User::factory()->create()->assignRole(UserRole::Admin->value);
    $manager = User::factory()->create()->assignRole(UserRole::Manager->value);
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);

    $lead = Lead::factory()->create();

    expect(Gate::forUser($owner)->allows('delete', $lead))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $lead))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('delete', $lead))->toBeFalse()
        ->and(Gate::forUser($sales)->allows('delete', $lead))->toBeFalse();
});

test('reopen is restricted to owner, admin, and manager', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $manager = User::factory()->create()->assignRole(UserRole::Manager->value);
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);

    $lead = Lead::factory()->create();

    expect(Gate::forUser($owner)->allows('reopen', $lead))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('reopen', $lead))->toBeTrue()
        ->and(Gate::forUser($sales)->allows('reopen', $lead))->toBeFalse();
});
