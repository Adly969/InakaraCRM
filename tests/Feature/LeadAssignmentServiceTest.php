<?php

use App\Enums\LeadStatus;
use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it assigns lead using round-robin distribution to eligible sales agents', function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    $assigner = User::factory()->create();

    // Create 3 sales agents
    $agentA = User::factory()->create()->assignRole(UserRole::Sales->value);
    $agentB = User::factory()->create()->assignRole(UserRole::Sales->value);
    $agentC = User::factory()->create()->assignRole(UserRole::Sales->value);

    // Seed some leads assigned to agents A and B to create timestamps
    $lead1 = Lead::factory()->create([
        'assigned_to' => $agentA->id,
        'updated_at' => now()->subDays(3),
    ]);

    $lead2 = Lead::factory()->create([
        'assigned_to' => $agentB->id,
        'updated_at' => now()->subDays(1),
    ]);

    // Agent C has NO leads assigned (oldest/null assignment updated_at)
    // When we assign a new lead, it should go to Agent C
    $newLead = Lead::factory()->create(['status' => LeadStatus::New]);

    $service = new LeadAssignmentService;
    $service->assignRoundRobin($newLead, $assigner);

    expect($newLead->fresh()->assigned_to)->toBe($agentC->id)
        ->and($newLead->fresh()->status)->toBe(LeadStatus::Assigned);
});
