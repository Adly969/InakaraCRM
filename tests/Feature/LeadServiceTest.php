<?php

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates a lead and generates a reference number', function () {
    $creator = User::factory()->create();
    $service = new LeadService;

    $data = [
        'name' => 'Jane Doe',
        'company_name' => 'Acme Corp',
        'email' => 'jane@example.com',
        'phone' => '1234567890',
        'source' => LeadSource::Marketing,
        'status' => LeadStatus::New,
    ];

    $lead = $service->create($data, $creator);

    expect($lead)->toBeInstanceOf(Lead::class)
        ->and($lead->name)->toBe('Jane Doe')
        ->and($lead->company_name)->toBe('Acme Corp')
        ->and($lead->email)->toBe('jane@example.com')
        ->and($lead->phone)->toBe('1234567890')
        ->and($lead->source)->toBe(LeadSource::Marketing)
        ->and($lead->status)->toBe(LeadStatus::New)
        ->and($lead->created_by)->toBe($creator->id)
        ->and($lead->reference_no)->toBe('LD-000001');

    $this->assertDatabaseHas('leads', [
        'id' => $lead->id,
        'reference_no' => 'LD-000001',
        'created_by' => $creator->id,
    ]);
});

test('it updates a lead and sets updated_by field', function () {
    $lead = Lead::factory()->create();
    $updater = User::factory()->create();
    $service = new LeadService;

    $data = [
        'name' => 'Jane Updated',
        'email' => 'jane.updated@example.com',
    ];

    $updatedLead = $service->update($lead, $data, $updater);

    expect($updatedLead->name)->toBe('Jane Updated')
        ->and($updatedLead->email)->toBe('jane.updated@example.com')
        ->and($updatedLead->updated_by)->toBe($updater->id);

    $this->assertDatabaseHas('leads', [
        'id' => $lead->id,
        'name' => 'Jane Updated',
        'updated_by' => $updater->id,
    ]);
});

test('it assigns a lead to a user and sets updated_by field', function () {
    $lead = Lead::factory()->create();
    $user = User::factory()->create();
    $updater = User::factory()->create();
    $service = new LeadService;

    $assignedLead = $service->assign($lead, $user, $updater);

    expect($assignedLead->assigned_to)->toBe($user->id)
        ->and($assignedLead->updated_by)->toBe($updater->id);

    $this->assertDatabaseHas('leads', [
        'id' => $lead->id,
        'assigned_to' => $user->id,
        'updated_by' => $updater->id,
    ]);

    // Test unassigning works
    $unassignedLead = $service->assign($assignedLead, null, $updater);
    expect($unassignedLead->assigned_to)->toBeNull();
});

test('it changes lead status to disqualified with a valid reason', function () {
    $lead = Lead::factory()->create();
    $updater = User::factory()->create();
    $service = new LeadService;

    $updatedLead = $service->changeStatus($lead, LeadStatus::Disqualified, 'Budget cut', $updater);

    expect($updatedLead->status)->toBe(LeadStatus::Disqualified)
        ->and($updatedLead->disqualification_reason)->toBe('Budget cut')
        ->and($updatedLead->updated_by)->toBe($updater->id);
});

test('it throws an exception when changing status to disqualified without a reason', function () {
    $lead = Lead::factory()->create();
    $updater = User::factory()->create();
    $service = new LeadService;

    expect(fn () => $service->changeStatus($lead, LeadStatus::Disqualified, '', $updater))
        ->toThrow(InvalidArgumentException::class, 'A disqualification reason is required when status is disqualified.');

    expect(fn () => $service->changeStatus($lead, LeadStatus::Disqualified, null, $updater))
        ->toThrow(InvalidArgumentException::class, 'A disqualification reason is required when status is disqualified.');
});

test('managers or owners can reopen a disqualified lead', function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    $manager = User::factory()->create()->assignRole(UserRole::Manager->value);
    $lead = Lead::factory()->disqualified('Not interested')->create();
    $service = new LeadService;

    $reopenedLead = $service->changeStatus($lead, LeadStatus::Contacted, null, $manager);

    expect($reopenedLead->status)->toBe(LeadStatus::Contacted)
        ->and($reopenedLead->disqualification_reason)->toBeNull()
        ->and($reopenedLead->updated_by)->toBe($manager->id);
});

test('sales representatives cannot reopen a disqualified lead', function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    $sales = User::factory()->create()->assignRole(UserRole::Sales->value);
    $lead = Lead::factory()->disqualified('Not interested')->create();
    $service = new LeadService;

    expect(fn () => $service->changeStatus($lead, LeadStatus::Contacted, null, $sales))
        ->toThrow(AuthorizationException::class, 'Only managers, admins, or owners can reopen a disqualified lead.');
});
