<?php
declare(strict_types=1);
namespace App\Enums;

enum Role: string
{
    case CITIZEN = 'CITOYEN';
    case AGENT   = 'AGENT';
    case ADMIN   = 'ADMIN';

    public function label(): string
    {
        return match ($this) {
            self::CITIZEN => 'Citoyen',
            self::AGENT   => 'Agent municipal',
            self::ADMIN   => 'Administrateur',
        };
    }
}
