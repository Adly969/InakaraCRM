<?php

namespace App\Enums;

enum CrmActivityStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Get the display color for UI badges.
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Completed => 'green',
            self::Cancelled => 'gray',
        };
    }
}
