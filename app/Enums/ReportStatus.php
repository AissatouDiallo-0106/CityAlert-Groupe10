<?php
declare(strict_types=1);
namespace App\Enums;

enum ReportStatus: string
{
    case NEW       = 'NOUVEAU';
    case IN_PROGRESS = 'EN_COURS';
    case RESOLVED  = 'RESOLU';
    case REJECTED  = 'REJETE';

    public function label(): string
    {
        return match ($this) {
            self::NEW         => 'Nouveau',
            self::IN_PROGRESS => 'En cours',
            self::RESOLVED    => 'Résolu',
            self::REJECTED    => 'Rejeté',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::NEW         => 'bg-secondary',
            self::IN_PROGRESS => 'bg-warning text-dark',
            self::RESOLVED    => 'bg-success',
            self::REJECTED    => 'bg-danger',
        };
    }

    /** Transitions autorisées (machine à états). @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::NEW         => [self::IN_PROGRESS, self::REJECTED],
            self::IN_PROGRESS => [self::RESOLVED, self::REJECTED],
            default           => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}