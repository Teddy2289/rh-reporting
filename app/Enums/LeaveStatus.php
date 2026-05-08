<?php

namespace App\Enums;

enum LeaveStatus: string
{
    case Pending  = 'pending';
    case Approved = 'approved';
    case Refused  = 'refused';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'En attente',
            self::Approved  => 'Approuvé',
            self::Refused   => 'Refusé',
            self::Cancelled => 'Annulé',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending   => 'yellow',
            self::Approved  => 'green',
            self::Refused   => 'red',
            self::Cancelled => 'gray',
        };
    }
}
