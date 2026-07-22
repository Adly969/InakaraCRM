<?php

use App\Enums\OpportunityStatus;
use App\Enums\UserRole;
use App\Models\CrmLossReason;
use App\Models\CrmPipelineStage;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
    $this->artisan('db:seed --class=CrmPipelineSeeder');
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

test('authorized users can view opportunity listing', function () {
    $manager = User::factory()->create()->assignRole(UserRole::Manager->value);
    $opportunity = Opportunity::factory()->create();

    $response = $this->actingAs($manager)->get(route('opportunities.index'));

    $response->assertStatus(200);
});

test('unauthorized users cannot view opportunity listing', function () {
    $viewer = User::factory()->create(); // No roles or permissions assigned

    $response = $this->actingAs($viewer)->get(route('opportunities.index'));

    $response->assertStatus(403);
});

test('authorized users can create opportunity', function () {
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);
    $customer = Customer::factory()->create();
    $stage = CrmPipelineStage::firstOrFail();

    $data = [
        'customer_id' => $customer->id,
        'title' => 'Direct Opp Deal',
        'pipeline_stage_id' => $stage->id,
        'deal_value' => 50000000,
        'expected_close_date' => now()->addMonth()->format('Y-m-d'),
        'assigned_to' => $sales->id,
    ];

    $response = $this->actingAs($sales)->post(route('opportunities.store'), $data);
    $response->assertSessionHasNoErrors();

    $opportunity = Opportunity::where('title', 'Direct Opp Deal')->firstOrFail();

    $response->assertRedirect(route('opportunities.show', $opportunity->id));
    $this->assertDatabaseHas('crm_opportunities', [
        'title' => 'Direct Opp Deal',
        'deal_value' => 50000000,
    ]);
});

test('users can win opportunity via workflow endpoint', function () {
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);
    $opportunity = Opportunity::factory()->create(['assigned_to' => $sales->id]);

    $response = $this->actingAs($sales)->post(route('opportunities.win', $opportunity->id));

    $response->assertStatus(302); // Redirect back
    expect($opportunity->fresh()->status)->toBe(OpportunityStatus::Won);
});

test('users can lose opportunity via workflow endpoint with reason', function () {
    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);
    $opportunity = Opportunity::factory()->create(['assigned_to' => $sales->id]);
    $reason = CrmLossReason::firstOrFail();

    $response = $this->actingAs($sales)->post(route('opportunities.lose', $opportunity->id), [
        'loss_reason_id' => $reason->id,
        'loss_notes' => 'Too high pricing',
    ]);

    $response->assertStatus(302);
    expect($opportunity->fresh()->status)->toBe(OpportunityStatus::Lost)
        ->and($opportunity->fresh()->loss_reason_id)->toBe($reason->id);
});
