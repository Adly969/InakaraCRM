<?php

use App\Models\User;
use App\Models\AuthenticationLog;
use Illuminate\Support\Facades\Hash;

test('successful login creates a login log entry', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('authentication_logs', [
        'user_id' => $user->id,
        'event' => 'login',
        'email' => $user->email,
    ]);
});

test('logout creates a logout log entry', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('logout'));

    $this->assertDatabaseHas('authentication_logs', [
        'user_id' => $user->id,
        'event' => 'logout',
    ]);
});

test('failed login attempt creates a failed log entry', function () {
    $email = 'failed_attempt@inakara.com';

    $response = $this->post(route('login'), [
        'email' => $email,
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertDatabaseHas('authentication_logs', [
        'user_id' => null,
        'event' => 'failed',
        'email' => $email,
    ]);
});
