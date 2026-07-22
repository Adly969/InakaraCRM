<?php

namespace App\Policies;

use App\Enums\Permission as AppPermission;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(AppPermission::ViewCustomers->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $customer): bool
    {
        if (! $user->hasPermissionTo(AppPermission::ViewCustomers->value)) {
            return false;
        }

        // Owners, Admins, Managers, and Customer Service can view all customers
        if ($user->hasRole(UserRole::Owner->value) ||
            $user->hasRole(UserRole::Admin->value) ||
            $user->hasRole(UserRole::Manager->value) ||
            $user->hasRole(UserRole::CustomerService->value)) {
            return true;
        }

        // Sales representatives can only view customers assigned to them
        return $customer->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(AppPermission::CreateCustomers->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $customer): bool
    {
        if (! $user->hasPermissionTo(AppPermission::EditCustomers->value)) {
            return false;
        }

        // Owners, Admins, and Managers can update all customers
        if ($user->hasRole(UserRole::Owner->value) ||
            $user->hasRole(UserRole::Admin->value) ||
            $user->hasRole(UserRole::Manager->value)) {
            return true;
        }

        // Sales representatives can only update customers assigned to them
        return $customer->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo(AppPermission::DeleteCustomers->value);
    }

    /**
     * Determine whether the user can merge customer accounts.
     */
    public function merge(User $user): bool
    {
        return $user->hasRole(UserRole::Owner->value) ||
               $user->hasRole(UserRole::Admin->value) ||
               $user->hasRole(UserRole::Manager->value);
    }

    /**
     * Determine whether the user can assign owners to the customer.
     */
    public function assign(User $user, Customer $customer): bool
    {
        return $this->update($user, $customer);
    }
}
