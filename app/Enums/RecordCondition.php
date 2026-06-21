<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RecordCondition: string implements HasColor, HasLabel
{
    case Mint = 'Mint';
    case NearMint = 'Near Mint';
    case VeryGood = 'Very Good';
    case Good = 'Good';
    case Fair = 'Fair';
    case Poor = 'Poor';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Mint, self::NearMint => 'success',
            self::VeryGood => 'info',
            self::Good, self::Fair => 'warning',
            self::Poor => 'danger',
        };
    }
}
