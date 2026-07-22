<?php

use App\Enums\UserRole;
use App\Models\User;

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
});

test('unauthorized users cannot view settings users page', function () {
    $salesUser = User::factory()->create();
    $salesUser->assignRole(UserRole::Sales->value);

    $response = $this
        ->actingAs($salesUser)
        ->get(route('settings.users.index'));

    $response->assertStatus(403);
});

test('authorized users can view settings users page', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole(UserRole::Admin->value);

    $response = $this
        ->actingAs($adminUser)
        ->get(route('settings.users.index'));

    $response->assertOk();
});

test('admin can create user', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole(UserRole::Admin->value);

    $response = $this
        ->actingAs($adminUser)
        ->post(route('settings.users.store'), [
            'name' => 'New Karyawan',
            'email' => 'karyawan@inakara.com',
            'phone' => '0812345678',
            'password' => 'password123',
            'role' => UserRole::Sales->value,
        ]);

    $response->assertRedirect(route('settings.users.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'karyawan@inakara.com',
        'name' => 'New Karyawan',
    ]);

    $createdUser = User::where('email', 'karyawan@inakara.com')->first();
    expect($createdUser)->not->toBeNull();
    expect($createdUser->hasRole(UserRole::Sales->value))->toBeTrue();
});

test('admin can update user details and role', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole(UserRole::Admin->value);

    $userToEdit = User::factory()->create();
    $userToEdit->assignRole(UserRole::Sales->value);

    $response = $this
        ->actingAs($adminUser)
        ->put(route('settings.users.update', $userToEdit->id), [
            'name' => 'Updated Name',
            'email' => 'updated@inakara.com',
            'phone' => '089999999',
            'role' => UserRole::Finance->value,
            'is_active' => true,
        ]);

    $response->assertRedirect(route('settings.users.index'));

    $userToEdit->refresh();
    expect($userToEdit->name)->toBe('Updated Name');
    expect($userToEdit->email)->toBe('updated@inakara.com');
    expect($userToEdit->hasRole(UserRole::Finance->value))->toBeTrue();
});

test('admin can delete user', function () {
    $adminUser = User::factory()->create();
    $adminUser->assignRole(UserRole::Admin->value);

    $userToDelete = User::factory()->create();
    $userToDelete->assignRole(UserRole::Sales->value);

    $response = $this
        ->actingAs($adminUser)
        ->delete(route('settings.users.destroy', $userToDelete->id));

    $response->assertRedirect(route('settings.users.index'));

    $this->assertSoftDeleted('users', [
        'id' => $userToDelete->id,
    ]);
});
