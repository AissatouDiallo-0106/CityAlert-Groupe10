<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Models\Entities\AbstractEntity;
use App\Models\Entities\User;

final class UserRepository extends AbstractRepository
{
    protected string $table = 'users';
    protected string $entityClass = User::class;

    public function findByEmail(string $email): ?User
    {
        $row = $this->fetchOne("SELECT * FROM users WHERE email = ?", [strtolower(trim($email))]);
        return $row ? User::fromArray($row) : null;
    }

    public function save(AbstractEntity $entity): AbstractEntity
    {
        /** @var User $entity */
        if ($entity->getId() === null) {
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)"
            );
            $stmt->execute([$entity->getName(), $entity->getEmail(), $entity->getPasswordHash(), $entity->getRole()->value]);
            $entity->setId((int) $this->pdo->lastInsertId());
        } else {
            $stmt = $this->pdo->prepare(
                "UPDATE users SET name=?, email=?, role=? WHERE id=?"
            );
            $stmt->execute([$entity->getName(), $entity->getEmail(), $entity->getRole()->value, $entity->getId()]);
        }
        return $entity;
    }
}
