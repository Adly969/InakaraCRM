<?php

namespace App\Services\CRM;

use App\Models\Activity;
use App\Models\CrmEventOutbox;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerContact;
use App\Models\FollowUp;
use App\Repositories\CRM\CustomerRepository;
use DomainException;
use Illuminate\Support\Facades\DB;

class CustomerMergeService
{
    public function __construct(
        protected CustomerRepository $customerRepository
    ) {}

    /**
     * Merge two customers.
     *
     * @throws DomainException
     */
    public function merge(int $targetId, int $sourceId, int $userId): Customer
    {
        if ($targetId === $sourceId) {
            throw new DomainException('Cannot merge a customer into itself.');
        }

        return DB::transaction(function () use ($targetId, $sourceId, $userId) {
            // Lock and retrieve both customer records
            $target = Customer::withoutGlobalScopes()->where('id', $targetId)->lockForUpdate()->first();
            $source = Customer::withoutGlobalScopes()->where('id', $sourceId)->lockForUpdate()->first();

            if (! $target || ! $source) {
                throw new DomainException('Target or source customer not found.');
            }

            // Tenant verification
            if ($target->tenant_id !== $source->tenant_id) {
                throw new DomainException('Target and source customers must belong to the same tenant.');
            }

            // Status checks
            if ($target->status === 'merged' || $source->status === 'merged') {
                throw new DomainException('Cannot merge already merged profiles.');
            }

            if ($target->status === 'archived' || $source->status === 'archived') {
                throw new DomainException('Archived profiles must be restored before merging.');
            }

            // Re-associate Contacts
            CustomerContact::where('customer_id', $source->id)
                ->update([
                    'customer_id' => $target->id,
                    'is_primary' => false, // Ensure we don't have multiple primary contacts
                ]);

            // Re-associate Addresses
            CustomerAddress::where('customer_id', $source->id)
                ->update([
                    'customer_id' => $target->id,
                    'is_primary' => false,
                ]);

            // Re-associate Activities
            Activity::where('customer_id', $source->id)
                ->update(['customer_id' => $target->id]);

            // Re-associate Follow-ups
            FollowUp::where('customer_id', $source->id)
                ->update(['customer_id' => $target->id]);

            // Update statuses
            $source->status = 'merged';
            $source->parent_id = $target->id;
            $source->version = $source->version + 1;
            $source->deleted_at = now();
            $source->deleted_by = $userId;
            $source->save();

            $target->version = $target->version + 1;
            $target->save();

            // Record system timeline activity on target customer
            Activity::create([
                'customer_id' => $target->id,
                'tenant_id' => $target->tenant_id,
                'type' => 'system',
                'subject' => 'Customer Profile Merged',
                'description' => "Merged duplicate customer profile '{$source->name}' into this profile.",
                'occurred_at' => now(),
                'created_by' => $userId,
            ]);

            // Save Outbox Event
            CrmEventOutbox::create([
                'tenant_id' => $target->tenant_id,
                'event_type' => 'App\Events\CRM\CustomerMerged',
                'payload' => [
                    'target_customer_id' => $target->id,
                    'source_customer_id' => $source->id,
                    'merged_by' => $userId,
                    'occurred_at' => now()->toIso8601String(),
                ],
            ]);

            return $target;
        });
    }
}
