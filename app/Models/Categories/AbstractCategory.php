<?php
declare(strict_types=1);
namespace App\Models\Categories;

/**
 * Catégorie de signalement — classe abstraite.
 * Chaque catégorie concrète définit son délai de traitement et sa priorité
 * par défaut : c'est le POLYMORPHISME demandé dans le sujet.
 */
abstract class AbstractCategory
{
    /** Code stocké en base (ex. VOIRIE). */
    abstract public function code(): string;

    /** Libellé affiché. */
    abstract public function label(): string;

    /** Délai de traitement cible, en jours. */
    abstract public function processingDays(): int;

    /** Priorité par défaut (1 = haute, 3 = basse). */
    abstract public function defaultPriority(): int;

    /** Icône Bootstrap associée. */
    public function icon(): string { return 'bi-exclamation-circle'; }

    public function priorityLabel(): string
    {
        return match ($this->defaultPriority()) {
            1 => 'Haute',
            2 => 'Moyenne',
            default => 'Basse',
        };
    }
}
