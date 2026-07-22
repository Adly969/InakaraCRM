<?php

namespace App\Repositories\CRM;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerRepository
{
    /**
     * Find a Customer by ID.
     */
    public function find(int $id): ?Customer
    {
        return Customer::find($id);
    }

    /**
     * Find a Customer with preloaded relationships.
     */
    public function findWithRelations(int $id, array $relations = ['contacts', 'addresses', 'tags']): ?Customer
    {
        $customer = Customer::with($relations)->find($id);

        return $customer instanceof Customer ? $customer : null;
    }

    /**
     * Save a Customer model.
     */
    public function save(Customer $customer): bool
    {
        return $customer->save();
    }

    /**
     * Create a new Customer.
     */
    public function create(array $attributes): Customer
    {
        $customer = Customer::create($attributes);
        if (! $customer instanceof Customer) {
            throw new \RuntimeException('Failed to create customer.');
        }

        return $customer;
    }

    /**
     * Delete a Customer.
     */
    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }

    /**
     * Get all Customers for the current tenant.
     * Note: Tenant isolation is handled automatically via global scopes.
     *
     * @return Collection<int, Customer>
     */
    public function allForTenant(): Collection
    {
        return Customer::all();
    }
}
