<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum Lunas',
            self::Paid => 'Lunas',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Unpaid => 'bg-amber-100 text-amber-800',
            self::Paid => 'bg-emerald-100 text-emerald-800',
        };
    }
}
