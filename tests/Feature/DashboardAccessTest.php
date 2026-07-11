<?php

use App\Models\User;
use App\Enums\UserRole;

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
});

test('unauthenticated guests are redirected to login', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('every system role can access the dashboard', function () {
    foreach (UserRole::cases() as $role) {
        $user = User::factory()->create();
        $user->assignRole($role->value);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }
});
