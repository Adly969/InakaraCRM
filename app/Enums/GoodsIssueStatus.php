<?php

namespace App\Enums;

enum GoodsIssueStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Cancelled = 'cancelled';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Issued => 'Issued (Posted)',
            self::Cancelled => 'Cancelled',
        };
    }
}
