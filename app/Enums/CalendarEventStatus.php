<?php

namespace App\Enums;

enum CalendarEventStatus: string
{
    case Confirmed = 'confirmed';
    case Tentative = 'tentative';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Confirmed => 'Confirmed',
            self::Tentative => 'Tentative',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Confirmed => 'emerald',
            self::Tentative => 'amber',
            self::Cancelled => 'gray',
        };
    }
}
