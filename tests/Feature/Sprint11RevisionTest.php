<?php

use App\Models\CrmPipelineStage;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\DuplicateDetectionService;
use App\Services\IdempotencyService;
use App\Services\TransitionValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tenant isolation scopes filter records by user company and branch', function () {
    $this->artisan('db:seed --class=CrmPipelineSeeder');

    // Create Tenant A user and record
    $userA = User::factory()->create(['company_id' => 1, 'branch_id' => 10]);
    $leadA = Lead::factory()->create(['company_id' => 1, 'branch_id' => 10, 'name' => 'Lead Tenant A']);

    // Create Tenant B user and record
    $userB = User::factory()->create(['company_id' => 2, 'branch_id' => 20]);
    $leadB = Lead::factory()->create(['company_id' => 2, 'branch_id' => 20, 'name' => 'Lead Tenant B']);

    // Act as User A
    $this->actingAs($userA);
    expect(Lead::count())->toBe(1)
        ->and(Lead::first()->name)->toBe('Lead Tenant A');

    // Act as User B
    $this->actingAs($userB);
    expect(Lead::count())->toBe(1)
        ->and(Lead::first()->name)->toBe('Lead Tenant B');
});

test('transition validator rejects skipping stages for normal user', function () {
    $this->artisan('db:seed --class=CrmPipelineSeeder');

    $user = User::factory()->create(); // Role: Sales/Regular User by default
    $stage1 = CrmPipelineStage::where('stage_sequence', 1)->firstOrFail();
    $stage3 = CrmPipelineStage::where('stage_sequence', 3)->firstOrFail(); // Skip stage 2

    $opportunity = Opportunity::factory()->create([
        'pipeline_stage_id' => $stage1->id,
    ]);

    $validator = new TransitionValidator;

    expect(fn () => $validator->validate($opportunity, $stage3, $user))
        ->toThrow(DomainException::class, 'Cannot skip pipeline stages');
});

test('duplicate detection service checks email and phone', function () {
    $this->artisan('db:seed --class=CrmPipelineSeeder');

    Lead::factory()->create([
        'email' => 'duplicate@example.com',
        'phone' => '08123456789',
    ]);

    $detector = new DuplicateDetectionService;

    $result = $detector->check([
        'email' => 'duplicate@example.com',
        'phone' => '08123456789',
    ]);

    expect($result['has_duplicates'])->toBeTrue()
        ->and($result['matches'][0]['similarity'])->toBe(100);
});

test('idempotency service prevents double execution', function () {
    $service = new IdempotencyService;
    $key = 'test_key_123';

    $counter = 0;
    $callback = function () use (&$counter) {
        $counter++;

        return 'success_val';
    };

    $res1 = $service->handle($key, $callback);
    $res2 = $service->handle($key, $callback);

    expect($res1)->toBe('success_val')
        ->and($res2)->toBe('success_val')
        ->and($counter)->toBe(1);
});
