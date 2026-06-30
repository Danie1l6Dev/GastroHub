<?php

namespace App\Enums;

enum TableAccountMode: string
{
    case Joint = 'joint';
    case Separate = 'separate';

    public function label(): string
    {
        return match ($this) {
            self::Joint => 'Pago en conjunto',
            self::Separate => 'Cuentas separadas',
        };
    }
}
