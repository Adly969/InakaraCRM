<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Issued = 'issued';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';
    case Void = 'void';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Approved => 'Approved',
            self::Issued => 'Issued',
            self::Overdue => 'Overdue',
            self::Cancelled => 'Cancelled',
            self::Void => 'Void',
        };
    }
}
