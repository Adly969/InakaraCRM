<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Company;
use App\Models\CrmEventOutbox;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerContact;
use App\Models\FollowUp;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CRM\CustomerMergeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerMergeTest extends TestCase {}

/** @var CustomerMergeTest $this */
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::create([
        'id' => Str::uuid(),
        'name' => 'Default Tenant',
        'slug' => 'default-tenant',
        'status' => 'active',
    ]);

    $this->company = Company::create([
        'tenant_id' => $this->tenant->id,
        'name' => 'PT Nusa Indah',
        'tax_id' => '12.345.678.9-000.000',
    ]);

    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'company_id' => $this->company->id,
    ]);

    app()->instance('current_tenant', $this->tenant);
});

test('it merges two active customer profiles successfully', function () {
    $target = Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'company_id' => $this->company->id,
        'status' => 'active',
        'version' => 1,
    ]);

    $source = Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'company_id' => $this->company->id,
        'status' => 'active',
        'version' => 1,
    ]);

    // Create child contacts, addresses, and activities
    $contact = CustomerContact::create([
        'customer_id' => $source->id,
        'tenant_id' => $source->tenant_id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'is_primary' => true,
        'status' => 'active',
    ]);

    $address = CustomerAddress::create([
        'customer_id' => $source->id,
        'tenant_id' => $source->tenant_id,
        'type' => 'billing',
        'street_address' => '123 Main St',
        'city' => 'Jakarta',
        'state_province' => 'DKI Jakarta',
        'postal_code' => '12345',
        'is_primary' => true,
    ]);

    $activity = Activity::create([
        'customer_id' => $source->id,
        'tenant_id' => $source->tenant_id,
        'type' => 'call',
        'subject' => 'Call discussion',
        'occurred_at' => now(),
        'created_by' => $this->user->id,
    ]);

    $followUp = FollowUp::create([
        'customer_id' => $source->id,
        'tenant_id' => $source->tenant_id,
        'title' => 'Send invoice',
        'due_date' => now()->addDays(2),
        'status' => 'pending',
    ]);

    $mergeService = app(CustomerMergeService::class);
    $result = $mergeService->merge($target->id, $source->id, $this->user->id);

    expect($result->id)->toBe($target->id);
    expect($result->version)->toBe(2);

    // Assert source status is merged
    $source->refresh();
    expect($source->status->value)->toBe('merged');
    expect((int) $source->parent_id)->toBe($target->id);

    // Assert relationships are re-associated to target
    expect(CustomerContact::where('customer_id', $target->id)->count())->toBe(1);
    expect(CustomerAddress::where('customer_id', $target->id)->count())->toBe(1);
    expect(Activity::where('customer_id', $target->id)->where('type', 'call')->count())->toBe(1);
    expect(FollowUp::where('customer_id', $target->id)->count())->toBe(1);

    // Verify system timeline activity was logged on target
    expect(Activity::where('customer_id', $target->id)->where('type', 'system')->count())->toBe(1);

    // Assert outbox event was logged
    expect(CrmEventOutbox::where('event_type', 'App\Events\CRM\CustomerMerged')->count())->toBe(1);
});

test('it blocks merge across different tenants', function () {
    $tenant2 = Tenant::create([
        'id' => Str::uuid(),
        'name' => 'Tenant 2',
        'slug' => 'tenant-2',
        'status' => 'active',
    ]);
    $company2 = Company::create([
        'tenant_id' => $tenant2->id,
        'name' => 'PT Indah Nusa',
        'tax_id' => '12.345.678.9-000.000',
    ]);

    $target = Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'company_id' => $this->company->id,
        'status' => 'active',
    ]);

    $source = Customer::factory()->create([
        'tenant_id' => $tenant2->id,
        'company_id' => $company2->id,
        'status' => 'active',
    ]);

    $mergeService = app(CustomerMergeService::class);

    $this->expectException(\DomainException::class);
    $this->expectExceptionMessage('Target and source customers must belong to the same tenant.');

    $mergeService->merge($target->id, $source->id, $this->user->id);
});

test('it blocks self merges', function () {
    $target = Customer::factory()->create([
        'tenant_id' => $this->tenant->id,
        'company_id' => $this->company->id,
        'status' => 'active',
    ]);

    $mergeService = app(CustomerMergeService::class);

    $this->expectException(\DomainException::class);
    $this->expectExceptionMessage('Cannot merge a customer into itself.');

    $mergeService->merge($target->id, $target->id, $this->user->id);
});
