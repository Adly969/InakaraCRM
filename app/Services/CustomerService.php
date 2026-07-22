<?php

namespace App\Services;

use App\Enums\CustomerStatus;
use App\Enums\LeadStatus;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;

class CustomerService
{
    /**
     * Create a new customer record and generate its reference number.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $creator): Customer
    {
        $customer = new Customer;
        $customer->fill($data);
        $customer->created_by = $creator->id;
        $customer->save();

        $customer->reference_no = 'CS-'.str_pad((string) $customer->id, 6, '0', STR_PAD_LEFT);
        $customer->saveQuietly();

        return $customer;
    }

    /**
     * Update a customer's basic attributes.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Customer $customer, array $data, User $updater): Customer
    {
        $customer->fill($data);
        $customer->updated_by = $updater->id;
        $customer->save();

        return $customer;
    }

    /**
     * Promote a Lead to a Customer record.
     */
    public function promoteLead(Lead $lead, User $creator): Customer
    {
        $existingCustomer = null;
        if (! empty($lead->email)) {
            $existingCustomer = Customer::where('email', $lead->email)->first();
        }
        if (! $existingCustomer && ! empty($lead->phone)) {
            $existingCustomer = Customer::where('phone', $lead->phone)->first();
        }

        if ($existingCustomer) {
            // Update Lead status to qualified
            $lead->status = LeadStatus::Qualified;
            $lead->updated_by = $creator->id;
            $lead->save();

            return $existingCustomer;
        }

        $customer = new Customer;
        $customer->name = $lead->name;
        $customer->company_name = $lead->company_name;
        $customer->email = $lead->email;
        $customer->phone = $lead->phone;
        $customer->assigned_to = $lead->assigned_to;
        $customer->type = ! empty($lead->company_name) ? 'organization' : 'individual';
        $customer->status = CustomerStatus::Active;
        $customer->created_by = $creator->id;
        $customer->save();

        $customer->reference_no = 'CS-'.str_pad((string) $customer->id, 6, '0', STR_PAD_LEFT);
        $customer->saveQuietly();

        // Update Lead status to qualified
        $lead->status = LeadStatus::Qualified;
        $lead->updated_by = $creator->id;
        $lead->save();

        return $customer;
    }
}
