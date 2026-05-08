<?php

namespace App\Enums;

enum LeaveType: string
{
    case Annual     = 'annual';
    case Sick       = 'sick';
    case Unpaid     = 'unpaid';
    case Maternity  = 'maternity';
    case Paternity  = 'paternity';
    case Special    = 'special';

    public function label(): string
    {
        return match($this) {
            self::Annual    => 'Congé annuel',
            self::Sick      => 'Arrêt maladie',
            self::Unpaid    => 'Congé sans solde',
            self::Maternity => 'Congé maternité',
            self::Paternity => 'Congé paternité',
            self::Special   => 'Congé spécial',
        };
    }

    public function deductsBalance(): bool
    {
        return match($this) {
            self::Annual    => true,
            self::Special   => true,
            default         => false,
        };
    }
}
