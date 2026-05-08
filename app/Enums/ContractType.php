<?php

namespace App\Enums;

enum ContractType: string
{
    case CDI       = 'cdi';
    case CDD       = 'cdd';
    case Freelance = 'freelance';
    case Intern    = 'intern';

    public function label(): string
    {
        return match($this) {
            self::CDI       => 'CDI',
            self::CDD       => 'CDD',
            self::Freelance => 'Freelance',
            self::Intern    => 'Stage',
        };
    }

    public function annualLeaveDays(): int
    {
        return match($this) {
            self::CDI       => 25,
            self::CDD       => 25,
            self::Freelance => 0,
            self::Intern    => 5,
        };
    }
}
