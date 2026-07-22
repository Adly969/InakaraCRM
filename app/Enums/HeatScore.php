<?php

namespace App\Enums;

enum HeatScore: string
{
    case Cold = 'cold';
    case Warm = 'warm';
    case Hot = 'hot';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Cold => 'Cold',
            self::Warm => 'Warm',
            self::Hot => 'Hot',
        };
    }

    /**
     * Get the display color for UI badges.
     */
    public function color(): string
    {
        return match ($this) {
            self::Cold => 'blue',
            self::Warm => 'amber',
            self::Hot => 'red',
        };
    }
}
