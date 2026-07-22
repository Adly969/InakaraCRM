<?php

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
});

test('unauthenticated users are redirected to login', function () {
    $this->get(route('leads.index'))->assertRedirect(route('login'));
});

test('index page lists all leads for owners, but only assigned leads for sales', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);

    $lead1 = Lead::factory()->create(['assigned_to' => $sales->id]);
    $lead2 = Lead::factory()->create(); // unassigned

    // Owner view
    $response = $this->actingAs($owner)->get(route('leads.index'));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('leads/index')
        ->and($page['props']['leads']['data'])->toHaveCount(2);

    // Sales view
    $response = $this->actingAs($sales)->get(route('leads.index'));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('leads/index')
        ->and($page['props']['leads']['data'])->toHaveCount(1);
});

test('create page renders with users list', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    User::factory()->count(2)->create();

    $response = $this->actingAs($owner)->get(route('leads.create'));
    $response->assertStatus(200);
    $page = $response->original->getData()['page'];
    expect($page['component'])->toBe('leads/create')
        ->and($page['props']['users'])->not->toBeNull();
});

test('store saves a new lead and generates reference number', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);

    $data = [
        'name' => 'Acme Lead',
        'company_name' => 'Acme Corp',
        'email' => 'acme@example.com',
        'phone' => '12345678',
        'source' => LeadSource::Marketing->value,
    ];

    $this->actingAs($owner)
        ->post(route('leads.store'), $data)
        ->assertRedirect(route('leads.index'));

    $this->assertDatabaseHas('leads', [
        'name' => 'Acme Lead',
        'reference_no' => 'LD-000001',
        'created_by' => $owner->id,
    ]);
});

test('update modifies the lead attributes', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $lead = Lead::factory()->create();

    $data = [
        'name' => 'Acme Updated',
        'company_name' => 'Acme Corp',
        'email' => 'acme@example.com',
        'phone' => '12345678',
        'source' => LeadSource::Marketing->value,
        'status' => LeadStatus::Contacted->value,
    ];

    $this->actingAs($owner)
        ->put(route('leads.update', $lead), $data)
        ->assertRedirect(route('leads.index'));

    $this->assertDatabaseHas('leads', [
        'id' => $lead->id,
        'name' => 'Acme Updated',
        'status' => LeadStatus::Contacted->value,
        'updated_by' => $owner->id,
    ]);
});

test('sales cannot delete leads', function () {
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);
    $lead = Lead::factory()->create();

    $this->actingAs($sales)
        ->delete(route('leads.destroy', $lead))
        ->assertStatus(403);
});

test('owner can delete leads', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $lead = Lead::factory()->create();

    $this->actingAs($owner)
        ->delete(route('leads.destroy', $lead))
        ->assertRedirect(route('leads.index'));

    $this->assertSoftDeleted('leads', ['id' => $lead->id]);
});

test('changeStatus updates lead status', function () {
    $owner = User::factory()->create()->assignRole(UserRole::Owner->value);
    $lead = Lead::factory()->create();

    $this->actingAs($owner)
        ->put(route('leads.status.update', $lead), [
            'status' => LeadStatus::Contacted->value,
        ])
        ->assertRedirect();

    expect($lead->fresh()->status)->toBe(LeadStatus::Contacted);
});

test('reopening disqualified lead is allowed for managers but forbidden for sales', function () {
    $manager = User::factory()->create()->assignRole(UserRole::Manager->value);
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);

    $lead = Lead::factory()->disqualified('Lost budget')->create(['assigned_to' => $sales->id]);

    // Sales cannot reopen
    $this->actingAs($sales)
        ->put(route('leads.status.update', $lead), [
            'status' => LeadStatus::Contacted->value,
        ])
        ->assertStatus(403);

    // Manager can reopen
    $this->actingAs($manager)
        ->put(route('leads.status.update', $lead), [
            'status' => LeadStatus::Contacted->value,
        ])
        ->assertRedirect();

    expect($lead->fresh()->status)->toBe(LeadStatus::Contacted);
});
