<?php

namespace App\Enums;

enum RateType: string
{
    case Spot = 'SPOT';
    case Average = 'AVERAGE';
    case Historical = 'HISTORICAL';
    case Closing = 'CLOSING';
    case Corporate = 'CORPORATE';
}
