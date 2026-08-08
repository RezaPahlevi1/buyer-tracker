<?php

namespace App\Enums;

enum UserRole: string
{
    case Superadmin = 'superadmin';
    case Sales = 'sales';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Superadmin',
            self::Sales => 'Sales',
        };
    }
}