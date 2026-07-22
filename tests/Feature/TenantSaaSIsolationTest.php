<?php

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('onboarding wizard creates complete tenant environment atomically', function () {
    expect(Tenant::count())->toBe(0);
    expect(User::count())->toBe(0);

    // Seed Spatie default permissions
    Artisan::call('db:seed', ['--class' => 'RoleAndPermissionSeeder']);

    $response = $this->post('/register', [
        'tenant_name' => 'Nusa Indah Manufacturing',
        'company_name' => 'PT Nusa Indah Sejahtera',
        'company_tax_id' => '12.345.678.9-000.000',
        'branch_name' => 'Surabaya Main Branch',
        'branch_code' => 'SUB-01',
        'owner_name' => 'Adly Rafi',
        'owner_email' => 'adly@nusaindah.co.id',
        'owner_password' => 'supersecretpassword123',
    ]);

    $response->assertRedirect(route('dashboard'));

    expect(Tenant::count())->toBe(1);
    $tenant = Tenant::first();
    expect($tenant->name)->toBe('Nusa Indah Manufacturing')
        ->and($tenant->slug)->toBe('nusa-indah-manufacturing');

    expect(Subscription::count())->toBe(1);
    expect(Company::count())->toBe(1);
    expect(Branch::count())->toBe(1);
    expect(User::count())->toBe(1);

    $user = User::first();
    expect($user->tenant_id)->toBe($tenant->id)
        ->and($user->email)->toBe('adly@nusaindah.co.id')
        ->and($user->hasRole(UserRole::Owner->value))->toBeTrue();
});

test('onboarding fails atomically and rolls back on validation or creation issue', function () {
    expect(Tenant::count())->toBe(0);

    $response = $this->post('/register', [
        'tenant_name' => '',
        'company_name' => '',
        'branch_name' => '',
        'branch_code' => '',
        'owner_name' => '',
        'owner_email' => 'invalid-email',
        'owner_password' => 'short',
    ]);

    $response->assertSessionHasErrors(['tenant_name', 'company_name', 'branch_name', 'branch_code', 'owner_name', 'owner_email', 'owner_password']);
    expect(Tenant::count())->toBe(0);
});

test('tenant isolation restricts user data queries to active tenant context', function () {
    $tenantA = Tenant::create([
        'name' => 'Tenant A',
        'slug' => 'tenant-a',
        'status' => 'active',
    ]);
    $userA = User::factory()->create([
        'tenant_id' => $tenantA->id,
        'email' => 'user@tenant-a.com',
        'is_active' => true,
    ]);

    $tenantB = Tenant::create([
        'name' => 'Tenant B',
        'slug' => 'tenant-b',
        'status' => 'active',
    ]);
    $userB = User::factory()->create([
        'tenant_id' => $tenantB->id,
        'email' => 'user@tenant-b.com',
        'is_active' => true,
    ]);

    $this->actingAs($userA);

    app()->instance('current_tenant', $tenantA);

    $users = User::all();
    expect($users->count())->toBe(1)
        ->and($users->first()->email)->toBe('user@tenant-a.com');
});

test('subscription gating blocks access if subscription is expired or suspended', function () {
    $tenant = Tenant::create([
        'name' => 'Trial Workspace',
        'slug' => 'trial-workspace',
        'status' => 'active',
    ]);

    Subscription::create([
        'tenant_id' => $tenant->id,
        'plan_name' => 'trial',
        'status' => 'expired',
        'starts_at' => now()->subDays(20),
        'ends_at' => now()->subDays(6),
        'grace_ends_at' => now()->subDays(1),
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'email' => 'user@trial.com',
        'is_active' => true,
    ]);

    $this->actingAs($user);
    app()->instance('current_tenant', $tenant);

    $response = $this->get('/dashboard');
    $response->assertRedirect(route('subscription.inactive'));
});
