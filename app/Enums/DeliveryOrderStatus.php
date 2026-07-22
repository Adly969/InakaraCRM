<?php

namespace App\Enums;

enum DeliveryOrderStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case PartiallyShipped = 'partially_shipped';
    case Shipped = 'shipped';
    case PartiallyDelivered = 'partially_delivered';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Approved => 'Approved',
            self::PartiallyShipped => 'Partially Shipped',
            self::Shipped => 'Shipped',
            self::PartiallyDelivered => 'Partially Delivered',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }
}
