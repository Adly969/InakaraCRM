<?php

use App\Enums\OpportunityStatus;
use App\Models\CrmLossReason;
use App\Models\CrmPipelineStage;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\PipelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it transitions opportunity stage and logs transition history', function () {
    $this->artisan('db:seed --class=CrmPipelineSeeder');

    $user = User::factory()->create();
    $stage1 = CrmPipelineStage::where('stage_sequence', 1)->firstOrFail();
    $stage2 = CrmPipelineStage::where('stage_sequence', 2)->firstOrFail();

    $opportunity = Opportunity::factory()->create([
        'pipeline_stage_id' => $stage1->id,
        'status' => OpportunityStatus::Qualification,
    ]);

    $service = app(PipelineService::class);
    $updatedOpportunity = $service->changeStage($opportunity, $stage2, $user);

    expect($updatedOpportunity->pipeline_stage_id)->toBe($stage2->id);

    $this->assertDatabaseHas('crm_stage_histories', [
        'opportunity_id' => $opportunity->id,
        'from_stage_id' => $stage1->id,
        'to_stage_id' => $stage2->id,
        'changed_by' => $user->id,
    ]);
});

test('it closes opportunity as Won', function () {
    $this->artisan('db:seed --class=CrmPipelineSeeder');

    $user = User::factory()->create();
    $opportunity = Opportunity::factory()->create(['status' => OpportunityStatus::Qualification]);

    $service = app(PipelineService::class);
    $service->win($opportunity, $user);

    expect($opportunity->fresh()->status)->toBe(OpportunityStatus::Won);
});

test('it closes opportunity as Lost and tracks reason', function () {
    $this->artisan('db:seed --class=CrmPipelineSeeder');

    $user = User::factory()->create();
    $opportunity = Opportunity::factory()->create(['status' => OpportunityStatus::Qualification]);
    $reason = CrmLossReason::firstOrFail();

    $service = app(PipelineService::class);
    $service->lose($opportunity, $reason->id, 'Too expensive', $user);

    expect($opportunity->fresh()->status)->toBe(OpportunityStatus::Lost)
        ->and($opportunity->fresh()->loss_reason_id)->toBe($reason->id)
        ->and($opportunity->fresh()->loss_notes)->toBe('Too expensive');
});
