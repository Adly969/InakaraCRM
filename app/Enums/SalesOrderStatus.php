<?php

namespace App\Enums;

enum SalesOrderStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Reserved = 'reserved';
    case InPreparation = 'in_preparation';
    case Shipped = 'shipped';
    case Billed = 'billed';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Closed = 'closed';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Confirmed => 'Confirmed',
            self::Cancelled => 'Cancelled',
            self::PendingApproval => 'Pending Approval',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Reserved => 'Reserved',
            self::InPreparation => 'In Preparation',
            self::Shipped => 'Shipped',
            self::Billed => 'Billed',
            self::PartiallyPaid => 'Partially Paid',
            self::Paid => 'Paid',
            self::Closed => 'Closed',
        };
    }
}
