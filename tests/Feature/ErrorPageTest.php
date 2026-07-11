<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('non-existent url renders custom 404 page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/some-random-route-that-does-not-exist');

    $response->assertStatus(404);
    $response->assertInertia(fn (Assert $page) => $page->component('errors/404'));
});

test('unauthorized route access renders custom 403 page', function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    $sales = User::factory()->create();
    $sales->assignRole(\App\Enums\UserRole::Sales->value);

    $response = $this->actingAs($sales)->get('/test-403-error-page-trigger');

    $response->assertStatus(403);
    $response->assertInertia(fn (Assert $page) => $page->component('errors/403'));
});

// Register the test route dynamically in web.php or handle it
beforeEach(function () {
    \Illuminate\Support\Facades\Route::get('/test-403-error-page-trigger', function () {
        abort(403);
    })->middleware('web');
});
