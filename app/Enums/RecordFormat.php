<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RecordFormat: string implements HasLabel
{
    case TwelveInch = '12"';
    case TenInch = '10"';
    case SevenInch = '7"';

    public function getLabel(): string
    {
        return $this->value;
    }
}
