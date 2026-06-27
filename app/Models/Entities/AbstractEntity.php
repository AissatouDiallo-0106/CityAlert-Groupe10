<?php
declare(strict_types=1);
namespace App\Models\Entities;

use App\Traits\Timestampable;

/**
 * Classe mère de toutes les entités persistées.
 * Démontre : classe abstraite, encapsulation, trait, hydratation polymorphe.
 */
abstract class AbstractEntity
{
    use Timestampable;

    protected ?int $id = null;

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    /** Hydrate une entité depuis une ligne SQL (late static binding). */
    public static function fromArray(array $row): static
    {
        $entity = new static();
        $entity->hydrate($row);
        return $entity;
    }

    /** Chaque entité décrit comment se remplir depuis une ligne. */
    abstract protected function hydrate(array $row): void;
}
