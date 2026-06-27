<?php
declare(strict_types=1);
namespace App\Repositories;

use PDO;
use App\Core\Database;
use App\Models\Entities\AbstractEntity;

/**
 * Base commune des repositories : accès PDO, requêtes préparées, hydratation.
 */
abstract class AbstractRepository implements RepositoryInterface
{
    protected PDO $pdo;
    protected string $table = '';
    protected string $entityClass = '';

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return AbstractEntity[] */
    protected function hydrateAll(array $rows): array
    {
        return array_map(fn(array $r) => ($this->entityClass)::fromArray($r), $rows);
    }

    public function find(int $id): ?AbstractEntity
    {
        $row = $this->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
        return $row ? ($this->entityClass)::fromArray($row) : null;
    }

    public function all(int $limit = 100, int $offset = 0): array
    {
        return $this->hydrateAll(
            $this->fetchAll("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT ? OFFSET ?", [$limit, $offset])
        );
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
