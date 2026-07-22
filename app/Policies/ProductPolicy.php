<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-products') || $user->hasPermissionTo('view-inventory');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('view-products') || $user->hasPermissionTo('view-inventory');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-products');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('edit-products');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('delete-products');
    }
}
