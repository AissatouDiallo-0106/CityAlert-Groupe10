<?php
declare(strict_types=1);
namespace App\Models\Entities;

final class Comment extends AbstractEntity
{
    private int $reportId = 0;
    private int $authorId = 0;
    private string $body = '';
    private ?string $authorName = null;

    public function getReportId(): int { return $this->reportId; }
    public function setReportId(int $v): void { $this->reportId = $v; }
    public function getAuthorId(): int { return $this->authorId; }
    public function setAuthorId(int $v): void { $this->authorId = $v; }
    public function getBody(): string { return $this->body; }
    public function setBody(string $v): void { $this->body = trim($v); }
    public function getAuthorName(): ?string { return $this->authorName; }

    protected function hydrate(array $row): void
    {
        $this->id = isset($row['id']) ? (int) $row['id'] : null;
        $this->reportId = (int) ($row['report_id'] ?? 0);
        $this->authorId = (int) ($row['author_id'] ?? 0);
        $this->setBody($row['body'] ?? '');
        $this->authorName = $row['author_name'] ?? null;
        $this->setCreatedAt($row['created_at'] ?? null);
    }
}
