<?php

namespace App\Services\CRM;

use App\Models\Activity;
use App\Models\CrmEventOutbox;
use App\Models\Customer;
use App\Models\CustomerOwnerHistory;
use App\Repositories\CRM\CustomerRepository;
use DomainException;
use Illuminate\Support\Facades\DB;

class CustomerAssignmentService
{
    public function __construct(
        protected CustomerRepository $customerRepository
    ) {}

    /**
     * Reassign customer owner.
     *
     * @throws DomainException
     */
    public function reassign(int $customerId, int $newOwnerId, int $userId, ?string $reason = null): Customer
    {
        return DB::transaction(function () use ($customerId, $newOwnerId, $userId, $reason) {
            $customer = $this->customerRepository->find($customerId);

            if (! $customer) {
                throw new DomainException('Customer not found.');
            }

            // Lock for concurrency
            $customer = Customer::where('id', $customerId)->lockForUpdate()->first();

            if (! $customer) {
                throw new DomainException('Customer not found.');
            }

            $oldOwnerId = $customer->assigned_to;

            if ($oldOwnerId === $newOwnerId) {
                return $customer; // No transfer needed if owner remains same
            }

            // Update customer owner
            $customer->assigned_to = $newOwnerId;
            $customer->version = $customer->version + 1;
            $this->customerRepository->save($customer);

            // Log owner history
            CustomerOwnerHistory::create([
                'tenant_id' => $customer->tenant_id,
                'customer_id' => $customer->id,
                'previous_owner_id' => $oldOwnerId,
                'new_owner_id' => $newOwnerId,
                'reason' => $reason,
                'transferred_by' => $userId,
                'transferred_at' => now(),
            ]);

            // Log timeline activity
            Activity::create([
                'customer_id' => $customer->id,
                'tenant_id' => $customer->tenant_id,
                'type' => 'system',
                'subject' => 'Account Owner Reassigned',
                'description' => 'Ownership transferred. Reason: '.($reason ?: 'None specified'),
                'occurred_at' => now(),
                'created_by' => $userId,
            ]);

            // Write transactional outbox log
            CrmEventOutbox::create([
                'tenant_id' => $customer->tenant_id,
                'event_type' => 'App\Events\CRM\CustomerAssigned',
                'payload' => [
                    'customer_id' => $customer->id,
                    'previous_owner_id' => $oldOwnerId,
                    'new_owner_id' => $newOwnerId,
                    'transferred_by' => $userId,
                    'reason' => $reason,
                    'occurred_at' => now()->toIso8601String(),
                ],
            ]);

            return $customer;
        });
    }
}
