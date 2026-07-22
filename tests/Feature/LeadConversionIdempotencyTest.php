<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CRM\LeadConversionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeadConversionIdempotencyTest extends TestCase {}

/** @var LeadConversionIdempotencyTest $this */
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

test('it converts lead to customer and subsequent calls return existing customer profile', function () {
    $lead = Lead::factory()->create([
        'tenant_id' => $this->tenant->id,
        'company_id' => $this->company->id,
        'company_name' => null,
        'first_name' => 'Alice',
        'last_name' => 'Smith',
        'email' => 'alice@example.com',
        'status' => 'new',
        'version' => 1,
    ]);

    $conversionService = app(LeadConversionService::class);

    // First conversion
    $customer1 = $conversionService->convert($lead->id, $this->user->id);
    expect($customer1->name)->toBe('Alice Smith');
    expect($customer1->type)->toBe('individual');

    // Lead status is updated to converted
    $lead->refresh();
    expect($lead->status->value)->toBe('converted');

    // Second conversion attempt should be idempotent and return the same customer
    $customer2 = $conversionService->convert($lead->id, $this->user->id);
    expect($customer2->id)->toBe($customer1->id);
});
