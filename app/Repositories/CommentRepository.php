<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Models\Entities\AbstractEntity;
use App\Models\Entities\Comment;

final class CommentRepository extends AbstractRepository
{
    protected string $table = 'comments';
    protected string $entityClass = Comment::class;

    /** @return Comment[] */
    public function forReport(int $reportId): array
    {
        $rows = $this->fetchAll(
            "SELECT c.*, u.name AS author_name FROM comments c
             JOIN users u ON u.id = c.author_id
             WHERE c.report_id = ? ORDER BY c.id ASC",
            [$reportId]
        );
        return array_map(fn(array $r) => Comment::fromArray($r), $rows);
    }

    public function save(AbstractEntity $entity): AbstractEntity
    {
        /** @var Comment $entity */
        $stmt = $this->pdo->prepare(
            "INSERT INTO comments (report_id, author_id, body) VALUES (?,?,?)"
        );
        $stmt->execute([$entity->getReportId(), $entity->getAuthorId(), $entity->getBody()]);
        $entity->setId((int) $this->pdo->lastInsertId());
        return $entity;
    }
}
