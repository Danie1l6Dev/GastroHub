<?php

namespace App\Enums;

enum TableStatus: string
{
    case Available = 'available';
    case Occupied = 'occupied';
    case PaymentPending = 'payment_pending';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Occupied => 'Ocupada',
            self::PaymentPending => 'Pendiente de pago',
        };
    }
}
