<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Models\Entities\AbstractEntity;
use App\Models\Entities\Report;
use App\Models\Entities\StatusHistory;

final class ReportRepository extends AbstractRepository
{
    protected string $table = 'reports';
    protected string $entityClass = Report::class;

    private const SELECT = "SELECT r.*, u.name AS author_name FROM reports r JOIN users u ON u.id = r.author_id";

    public function findWithAuthor(int $id): ?Report
    {
        $row = $this->fetchOne(self::SELECT . " WHERE r.id = ?", [$id]);
        return $row ? Report::fromArray($row) : null;
    }

    /**
     * Recherche filtrée + paginée.
     * @param array{status?:string,category?:string,author_id?:int} $filters
     * @return Report[]
     */
    public function search(array $filters, int $page = 1, int $perPage = 8): array
    {
        [$where, $params] = $this->buildWhere($filters);
        $offset = ($page - 1) * $perPage;
        $rows = $this->fetchAll(
            self::SELECT . $where . " ORDER BY r.id DESC LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );
        return array_map(fn(array $r) => Report::fromArray($r), $rows);
    }

    public function countSearch(array $filters): int
    {
        [$where, $params] = $this->buildWhere($filters);
        $row = $this->fetchOne("SELECT COUNT(*) AS n FROM reports r" . $where, $params);
        return (int) ($row['n'] ?? 0);
    }

    private function buildWhere(array $filters): array
    {
        $conds = [];
        $params = [];
        if (!empty($filters['status']))   { $conds[] = 'r.status = ?';      $params[] = $filters['status']; }
        if (!empty($filters['category'])) { $conds[] = 'r.category = ?';    $params[] = $filters['category']; }
        if (!empty($filters['author_id'])){ $conds[] = 'r.author_id = ?';   $params[] = (int) $filters['author_id']; }
        $where = $conds ? ' WHERE ' . implode(' AND ', $conds) : '';
        return [$where, $params];
    }

    public function save(AbstractEntity $entity): AbstractEntity
    {
        /** @var Report $entity */
        if ($entity->getId() === null) {
            $stmt = $this->pdo->prepare(
                "INSERT INTO reports (title, description, category, address, photo, author_id, status)
                 VALUES (?,?,?,?,?,?,?)"
            );
            $stmt->execute([
                $entity->getTitle(), $entity->getDescription(), $entity->getCategory()->code(),
                $entity->getAddress(), $entity->getPhoto(), $entity->getAuthorId(), $entity->getStatus()->value,
            ]);
            $entity->setId((int) $this->pdo->lastInsertId());
        } else {
            $stmt = $this->pdo->prepare(
                "UPDATE reports SET title=?, description=?, category=?, address=?, photo=?, agent_id=?, status=?, updated_at=NOW() WHERE id=?"
            );
            $stmt->execute([
                $entity->getTitle(), $entity->getDescription(), $entity->getCategory()->code(),
                $entity->getAddress(), $entity->getPhoto(), $entity->getAgentId(),
                $entity->getStatus()->value, $entity->getId(),
            ]);
        }
        return $entity;
    }

    /** Historise un changement de statut. */
    public function addHistory(StatusHistory $h): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO status_history (report_id, agent_id, status, comment) VALUES (?,?,?,?)"
        );
        $stmt->execute([$h->getReportId(), $h->getAgentId(), $h->getStatus()->value, $h->getComment()]);
    }

    /** @return StatusHistory[] */
    public function historyFor(int $reportId): array
    {
        $rows = $this->fetchAll(
            "SELECT h.*, u.name AS agent_name FROM status_history h
             LEFT JOIN users u ON u.id = h.agent_id
             WHERE h.report_id = ? ORDER BY h.id ASC",
            [$reportId]
        );
        return array_map(fn(array $r) => StatusHistory::fromArray($r), $rows);
    }

    /** Statistiques pour l'administrateur. */
    public function statsByStatus(): array
    {
        return $this->fetchAll("SELECT status, COUNT(*) AS n FROM reports GROUP BY status");
    }
    public function statsByCategory(): array
    {
        return $this->fetchAll("SELECT category, COUNT(*) AS n FROM reports GROUP BY category");
    }
    public function averageResolutionDays(): ?float
    {
        $row = $this->fetchOne(
            "SELECT AVG(DATEDIFF(updated_at, created_at)) AS d FROM reports WHERE status = 'RESOLU'"
        );
        return $row && $row['d'] !== null ? round((float) $row['d'], 1) : null;
    }
}
