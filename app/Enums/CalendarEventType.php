<?php

namespace App\Enums;

enum CalendarEventType: string
{
    case Meeting = 'meeting';
    case Call = 'call';
    case SiteVisit = 'site_visit';
    case Reminder = 'reminder';
    case Deadline = 'deadline';

    public function label(): string
    {
        return match ($this) {
            self::Meeting => 'Meeting',
            self::Call => 'Call',
            self::SiteVisit => 'Site Visit',
            self::Reminder => 'Reminder',
            self::Deadline => 'Deadline',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Meeting => '#0284c7',
            self::Call => '#10b981',
            self::SiteVisit => '#f59e0b',
            self::Reminder => '#8b5cf6',
            self::Deadline => '#ef4444',
        };
    }
}
