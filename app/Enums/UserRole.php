<?php

namespace App\Enums;

enum UserRole: string
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
}
