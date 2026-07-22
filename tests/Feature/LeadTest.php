<?php

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('lead can be created using factory', function () {
    $lead = Lead::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    expect($lead)->toBeInstanceOf(Lead::class)
        ->name->toBe('John Doe')
        ->email->toBe('john@example.com')
        ->status->toBe(LeadStatus::New)
        ->source->toBeInstanceOf(LeadSource::class);
});

test('lead relationships can be accessed', function () {
    $user = User::factory()->create();
    $lead = Lead::factory()->create([
        'assigned_to' => $user->id,
        'created_by' => $user->id,
    ]);

    expect($lead->assignedTo)->toBeInstanceOf(User::class)
        ->and($lead->creator)->toBeInstanceOf(User::class);
});

test('lead disqualified state works correctly', function () {
    $lead = Lead::factory()->disqualified('Not interested')->create();

    expect($lead->status)->toBe(LeadStatus::Disqualified)
        ->and($lead->disqualification_reason)->toBe('Not interested');
});
