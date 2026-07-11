<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('inactive user is logged out and redirected to login', function () {
    $user = User::factory()->inactive()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['email' => 'Your account has been deactivated.']);
    expect(Auth::check())->toBeFalse();
});

test('active user can access the dashboard', function () {
    // Seed roles and permissions first
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    $user = User::factory()->create();
    $user->assignRole(UserRole::Sales->value);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    expect(Auth::check())->toBeTrue();
});
