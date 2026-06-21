<?php

namespace App\Enums;

enum RecordCondition: string
{
    case Mint = 'Mint';
    case NearMint = 'Near Mint';
    case VeryGood = 'Very Good';
    case Good = 'Good';
    case Fair = 'Fair';
    case Poor = 'Poor';
}
