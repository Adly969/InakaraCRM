<?php

use App\Enums\Permission as AppPermission;
use App\Enums\UserRole;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('roles and permissions are seeded correctly', function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    // Assert all cases of UserRole exist in db
    foreach (UserRole::cases() as $role) {
        $this->assertDatabaseHas('roles', [
            'name' => $role->value,
            'guard_name' => 'web',
        ]);
    }

    // Assert all cases of Permission exist in db
    foreach (AppPermission::cases() as $permission) {
        $this->assertDatabaseHas('permissions', [
            'name' => $permission->value,
            'guard_name' => 'web',
        ]);
    }
});

test('admin role has all foundation permissions', function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    $adminRole = Role::findByName(UserRole::Admin->value, 'web');

    foreach (AppPermission::cases() as $permission) {
        expect($adminRole->hasPermissionTo($permission->value))->toBeTrue();
    }
});

test('non-admin roles only have their expected permissions', function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    $salesRole = Role::findByName(UserRole::Sales->value, 'web');
    $managerRole = Role::findByName(UserRole::Manager->value, 'web');
    $financeRole = Role::findByName(UserRole::Finance->value, 'web');
    $csRole = Role::findByName(UserRole::CustomerService->value, 'web');

    // Sales permissions checks
    expect($salesRole->hasPermissionTo(AppPermission::ViewDashboard->value))->toBeTrue()
        ->and($salesRole->hasPermissionTo(AppPermission::ViewLeads->value))->toBeTrue()
        ->and($salesRole->hasPermissionTo(AppPermission::CreateLeads->value))->toBeTrue()
        ->and($salesRole->hasPermissionTo(AppPermission::EditLeads->value))->toBeTrue()
        ->and($salesRole->hasPermissionTo(AppPermission::DeleteLeads->value))->toBeFalse()
        ->and($salesRole->hasPermissionTo(AppPermission::ViewSettings->value))->toBeFalse()
        ->and($salesRole->hasPermissionTo(AppPermission::ViewUsers->value))->toBeFalse()
        ->and($salesRole->hasPermissionTo(AppPermission::ViewCustomers->value))->toBeTrue()
        ->and($salesRole->hasPermissionTo(AppPermission::CreateCustomers->value))->toBeTrue()
        ->and($salesRole->hasPermissionTo(AppPermission::EditCustomers->value))->toBeTrue()
        ->and($salesRole->hasPermissionTo(AppPermission::DeleteCustomers->value))->toBeFalse();

    // Manager permissions checks
    expect($managerRole->hasPermissionTo(AppPermission::ViewDashboard->value))->toBeTrue()
        ->and($managerRole->hasPermissionTo(AppPermission::ViewSettings->value))->toBeTrue()
        ->and($managerRole->hasPermissionTo(AppPermission::ViewLeads->value))->toBeTrue()
        ->and($managerRole->hasPermissionTo(AppPermission::CreateLeads->value))->toBeTrue()
        ->and($managerRole->hasPermissionTo(AppPermission::EditLeads->value))->toBeTrue()
        ->and($managerRole->hasPermissionTo(AppPermission::DeleteLeads->value))->toBeFalse()
        ->and($managerRole->hasPermissionTo(AppPermission::ViewUsers->value))->toBeFalse()
        ->and($managerRole->hasPermissionTo(AppPermission::ViewCustomers->value))->toBeTrue()
        ->and($managerRole->hasPermissionTo(AppPermission::CreateCustomers->value))->toBeTrue()
        ->and($managerRole->hasPermissionTo(AppPermission::EditCustomers->value))->toBeTrue()
        ->and($managerRole->hasPermissionTo(AppPermission::DeleteCustomers->value))->toBeFalse();

    // Finance permission checks
    expect($financeRole->hasPermissionTo(AppPermission::ViewDashboard->value))->toBeTrue()
        ->and($financeRole->hasPermissionTo(AppPermission::ViewLeads->value))->toBeFalse()
        ->and($financeRole->hasPermissionTo(AppPermission::ViewSettings->value))->toBeFalse()
        ->and($financeRole->hasPermissionTo(AppPermission::ViewCustomers->value))->toBeFalse();

    // Customer Service permission checks
    expect($csRole->hasPermissionTo(AppPermission::ViewDashboard->value))->toBeTrue()
        ->and($csRole->hasPermissionTo(AppPermission::ViewCustomers->value))->toBeTrue()
        ->and($csRole->hasPermissionTo(AppPermission::CreateCustomers->value))->toBeFalse();
});
