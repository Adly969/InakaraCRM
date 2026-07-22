<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShipmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can dispatch shipments.
     */
    public function dispatch(User $user, Shipment $shipment): bool
    {
        return $user->hasPermissionTo('dispatch-shipments') && $shipment->status === 'pending_dispatch';
    }

    /**
     * Determine whether the user can confirm delivery of the shipment.
     */
    public function confirm(User $user, Shipment $shipment): bool
    {
        return $user->hasPermissionTo('confirm-deliveries') && $shipment->status === 'in_transit';
    }
}
