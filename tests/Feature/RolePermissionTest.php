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

test('non-admin roles only have view-dashboard by default', function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    $salesRole = Role::findByName(UserRole::Sales->value, 'web');

    expect($salesRole->hasPermissionTo(AppPermission::ViewDashboard->value))->toBeTrue();
    expect($salesRole->hasPermissionTo(AppPermission::ViewSettings->value))->toBeFalse();
    expect($salesRole->hasPermissionTo(AppPermission::ViewUsers->value))->toBeFalse();
});
