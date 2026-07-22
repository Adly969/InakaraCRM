<?php

namespace App\Policies;

use App\Models\DeliveryOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeliveryOrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any delivery orders.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-delivery-orders');
    }

    /**
     * Determine whether the user can view the delivery order.
     */
    public function view(User $user, DeliveryOrder $deliveryOrder): bool
    {
        return $user->hasPermissionTo('view-delivery-orders');
    }

    /**
     * Determine whether the user can create delivery orders.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-delivery-orders');
    }

    /**
     * Determine whether the user can update the delivery order.
     */
    public function update(User $user, DeliveryOrder $deliveryOrder): bool
    {
        return $user->hasPermissionTo('create-delivery-orders') && $deliveryOrder->status === 'draft';
    }

    /**
     * Determine whether the user can approve the delivery order.
     */
    public function approve(User $user, DeliveryOrder $deliveryOrder): bool
    {
        return $user->hasPermissionTo('approve-delivery-orders') && $deliveryOrder->status === 'draft';
    }

    /**
     * Determine whether the user can cancel the delivery order.
     */
    public function cancel(User $user, DeliveryOrder $deliveryOrder): bool
    {
        return $user->hasPermissionTo('cancel-delivery-orders') && ! in_array($deliveryOrder->status, ['delivered', 'cancelled']);
    }
}
