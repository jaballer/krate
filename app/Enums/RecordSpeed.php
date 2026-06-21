<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RecordSpeed: string implements HasLabel
{
    case Rpm33 = '33 1/3 RPM';
    case Rpm45 = '45 RPM';
    case Rpm78 = '78 RPM';

    public function getLabel(): string
    {
        return $this->value;
    }
}
