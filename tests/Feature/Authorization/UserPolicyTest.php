<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
});

test('user with view-users permission can view users list', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);

    $sales = User::factory()->create();
    $sales->assignRole(UserRole::Sales->value);

    expect(Gate::forUser($admin)->allows('viewAny', User::class))->toBeTrue();
    expect(Gate::forUser($sales)->allows('viewAny', User::class))->toBeFalse();
});

test('owner can access everything regardless of explicit permission checks', function () {
    $owner = User::factory()->create();
    $owner->assignRole(UserRole::Owner->value);

    // Give owner no permissions explicitly, check if they can still pass Gate checks
    expect(Gate::forUser($owner)->allows('create', User::class))->toBeTrue();
    expect(Gate::forUser($owner)->allows('delete', User::factory()->create()))->toBeTrue();
});

test('user cannot delete themselves', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);

    expect(Gate::forUser($admin)->allows('delete', $admin))->toBeFalse();
});

test('non-owner cannot delete owner user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);

    $owner = User::factory()->create();
    $owner->assignRole(UserRole::Owner->value);

    expect(Gate::forUser($admin)->allows('delete', $owner))->toBeFalse();
});
