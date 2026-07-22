<?php

namespace App\Services\CRM;

use App\Enums\LeadStatus;
use App\Models\CrmEventOutbox;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Lead;
use App\Repositories\CRM\CustomerRepository;
use App\Repositories\CRM\LeadRepository;
use DomainException;
use Illuminate\Support\Facades\DB;

class LeadConversionService
{
    public function __construct(
        protected LeadRepository $leadRepository,
        protected CustomerRepository $customerRepository
    ) {}

    /**
     * Convert qualified lead to customer.
     *
     * @throws DomainException
     */
    public function convert(int $leadId, int $userId): Customer
    {
        return DB::transaction(function () use ($leadId, $userId) {
            $lead = $this->leadRepository->find($leadId);

            if (! $lead) {
                throw new DomainException('Lead not found.');
            }

            // Lock row for concurrency
            $lead = Lead::where('id', $leadId)->lockForUpdate()->first();

            if (! $lead) {
                throw new DomainException('Lead not found.');
            }

            // Lead conversion idempotency check
            $isConverted = $lead->status instanceof LeadStatus
                ? $lead->status->value === 'converted'
                : $lead->status === 'converted';

            if ($isConverted) {
                // Return existing customer profile if already converted
                $existing = Customer::where('email', $lead->email)
                    ->where('tenant_id', $lead->tenant_id)
                    ->first();
                if ($existing) {
                    return $existing;
                }
                throw new DomainException('Lead has already been converted.');
            }

            // Create customer
            $customer = $this->customerRepository->create([
                'tenant_id' => $lead->tenant_id,
                'company_id' => $lead->company_id,
                'branch_id' => $lead->branch_id,
                'name' => $lead->company_name ?: ($lead->first_name.' '.$lead->last_name),
                'type' => $lead->company_name ? 'company' : 'individual',
                'email' => $lead->email,
                'phone' => $lead->phone,
                'status' => 'active',
                'assigned_to' => $lead->assigned_to ?: $userId,
                'source' => $lead->source,
                'version' => 1,
                'created_by' => $userId,
            ]);

            // Seed primary contact
            CustomerContact::create([
                'customer_id' => $customer->id,
                'tenant_id' => $customer->tenant_id,
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'is_primary' => true,
                'status' => 'active',
                'version' => 1,
                'created_by' => $userId,
            ]);

            // Update Lead Status
            $lead->status = 'converted';
            $lead->version = $lead->version + 1;
            $this->leadRepository->save($lead);

            // Write transactional outbox log
            CrmEventOutbox::create([
                'tenant_id' => $customer->tenant_id,
                'event_type' => 'App\Events\CRM\LeadConverted',
                'payload' => [
                    'lead_id' => $lead->id,
                    'customer_id' => $customer->id,
                    'converted_by' => $userId,
                    'occurred_at' => now()->toIso8601String(),
                ],
            ]);

            return $customer;
        });
    }
}
