<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Assigned = 'assigned';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Converted = 'converted';
    case Disqualified = 'disqualified';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Assigned => 'Assigned',
            self::Contacted => 'Contacted',
            self::Qualified => 'Qualified',
            self::Converted => 'Converted',
            self::Disqualified => 'Disqualified',
        };
    }

    /**
     * Get the display color for UI badges.
     */
    public function color(): string
    {
        return match ($this) {
            self::New => 'gray',
            self::Assigned => 'blue',
            self::Contacted => 'indigo',
            self::Qualified => 'emerald',
            self::Converted => 'green',
            self::Disqualified => 'red',
        };
    }
}
