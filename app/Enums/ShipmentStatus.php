<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case PendingDispatch = 'pending_dispatch';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case FailedDelivery = 'failed_delivery';
    case Returned = 'returned';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PendingDispatch => 'Pending Dispatch',
            self::InTransit => 'In Transit',
            self::Delivered => 'Delivered',
            self::FailedDelivery => 'Failed Delivery',
            self::Returned => 'Returned',
            self::Cancelled => 'Cancelled',
        };
    }
}
