<?php

namespace App\Enums;

enum LeadSource: string
{
    case Referral = 'referral';
    case Marketing = 'marketing';
    case WalkIn = 'walk_in';
    case Phone = 'phone';
    case Digital = 'digital';
    case Event = 'event';
}
