<?php

namespace App\Enums;

enum ActivityOutcome: string
{
    case Interested = 'interested';
    case NotInterested = 'not_interested';
    case FollowUp = 'follow_up';
    case NoAnswer = 'no_answer';
    case DealClosed = 'deal_closed';

    public function label(): string
    {
        return match ($this) {
            self::Interested => 'Interested',
            self::NotInterested => 'Not Interested',
            self::FollowUp => 'Follow Up',
            self::NoAnswer => 'No Answer',
            self::DealClosed => 'Deal Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Interested => 'emerald',
            self::NotInterested => 'rose',
            self::FollowUp => 'amber',
            self::NoAnswer => 'gray',
            self::DealClosed => 'sky',
        };
    }
}
