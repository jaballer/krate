<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasLabel
{
    case Administrator = 'Administrator';
    case Manager = 'Manager';
    case StandardUser = 'Standard User';
    case Guest = 'Guest';

    /** Staff may access the Filament admin panel (see #28). */
    public function isStaff(): bool
    {
        return in_array($this, [self::Administrator, self::Manager], true);
    }

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Administrator => 'danger',
            self::Manager => 'warning',
            self::StandardUser => 'gray',
            self::Guest => 'info',
        };
    }
}
