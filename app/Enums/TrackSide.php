<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TrackSide: string implements HasLabel
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';

    public function getLabel(): string
    {
        return 'Side '.$this->value;
    }
}
