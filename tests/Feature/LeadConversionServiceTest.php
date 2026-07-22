<?php

use App\Enums\LeadStatus;
use App\Enums\OpportunityStatus;
use App\Events\LeadConverted;
use App\Events\OpportunityCreated;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\LeadConversionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('it converts qualified lead to opportunity successfully', function () {
    $this->artisan('db:seed --class=CrmPipelineSeeder');
    Event::fake();

    $converter = User::factory()->create();
    $customer = Customer::factory()->create();
    $lead = Lead::factory()->create([
        'status' => LeadStatus::Qualified,
        'assigned_to' => $converter->id,
    ]);

    $data = [
        'customer_id' => $customer->id,
        'title' => 'Big Enterprise Licensing Deal',
        'deal_value' => 75000000.00,
        'expected_close_date' => now()->addMonths(2)->format('Y-m-d'),
    ];

    $service = new LeadConversionService;
    $opportunity = $service->convert($lead, $data, $converter);

    expect($opportunity)->toBeInstanceOf(Opportunity::class)
        ->and($opportunity->title)->toBe('Big Enterprise Licensing Deal')
        ->and((float) $opportunity->deal_value)->toBe(75000000.00)
        ->and($opportunity->status)->toBe(OpportunityStatus::Qualification)
        ->and($lead->fresh()->status)->toBe(LeadStatus::Converted);

    Event::assertDispatched(LeadConverted::class);
    Event::assertDispatched(OpportunityCreated::class);
});

test('it throws exception when converting unqualified lead', function () {
    $this->artisan('db:seed --class=CrmPipelineSeeder');

    $converter = User::factory()->create();
    $customer = Customer::factory()->create();
    $lead = Lead::factory()->create([
        'status' => LeadStatus::New,
    ]);

    $data = [
        'customer_id' => $customer->id,
        'title' => 'Invalid Conversion Deal',
        'deal_value' => 500000.00,
        'expected_close_date' => now()->addWeek()->format('Y-m-d'),
    ];

    $service = new LeadConversionService;

    expect(fn () => $service->convert($lead, $data, $converter))
        ->toThrow(DomainException::class, 'Only qualified leads can be converted to opportunities.');
});
