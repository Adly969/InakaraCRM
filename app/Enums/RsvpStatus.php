<?php

namespace App\Enums;

enum RsvpStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Tentative = 'tentative';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Accepted => 'Accepted',
            self::Declined => 'Declined',
            self::Tentative => 'Tentative',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Accepted => 'emerald',
            self::Declined => 'rose',
            self::Tentative => 'gray',
        };
    }
}
