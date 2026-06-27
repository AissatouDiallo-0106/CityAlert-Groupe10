<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Models\Entities\AbstractEntity;

/**
 * Contrat commun à tous les repositories (patron Repository).
 */
interface RepositoryInterface
{
    public function find(int $id): ?AbstractEntity;
    public function all(int $limit = 100, int $offset = 0): array;
    public function save(AbstractEntity $entity): AbstractEntity;
    public function delete(int $id): bool;
}
