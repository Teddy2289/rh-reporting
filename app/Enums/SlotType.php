<?php

namespace App\Enums;

enum SlotType: string
{
    case Work  = 'work';
    case Pause = 'pause';
    case Leave = 'leave';
    case Holiday = 'holiday';

    public function label(): string
    {
        return match($this) {
            self::Work    => 'Travail',
            self::Pause   => 'Pause',
            self::Leave   => 'Congé',
            self::Holiday => 'Jour férié',
        };
    }

    public function isWorked(): bool
    {
        return $this === self::Work;
    }
}
