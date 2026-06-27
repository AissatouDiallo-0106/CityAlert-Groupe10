<?php
declare(strict_types=1);
namespace App\Models\Entities;

use App\Enums\ReportStatus;
use App\Models\Categories\AbstractCategory;
use App\Models\Categories\CategoryFactory;

final class Report extends AbstractEntity
{
    private string $title = '';
    private string $description = '';
    private string $address = '';
    private ?string $photo = null;
    private int $authorId = 0;
    private ?int $agentId = null;
    private AbstractCategory $category;
    private ReportStatus $status = ReportStatus::NEW;

    // Champs joints (lecture seule)
    private ?string $authorName = null;

    public function __construct()
    {
        $this->category = CategoryFactory::make('VOIRIE');
    }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $v): void { $this->title = trim($v); }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $v): void { $this->description = trim($v); }

    public function getAddress(): string { return $this->address; }
    public function setAddress(string $v): void { $this->address = trim($v); }

    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(?string $v): void { $this->photo = $v ?: null; }

    public function getAuthorId(): int { return $this->authorId; }
    public function setAuthorId(int $v): void { $this->authorId = $v; }

    public function getAgentId(): ?int { return $this->agentId; }
    public function setAgentId(?int $v): void { $this->agentId = $v; }

    public function getCategory(): AbstractCategory { return $this->category; }
    public function setCategory(AbstractCategory|string $v): void
    {
        $this->category = $v instanceof AbstractCategory ? $v : CategoryFactory::make($v);
    }

    public function getStatus(): ReportStatus { return $this->status; }
    public function setStatus(ReportStatus|string $v): void { $this->status = $v instanceof ReportStatus ? $v : ReportStatus::from($v); }

    public function getAuthorName(): ?string { return $this->authorName; }

    /** Un signalement n'est modifiable/supprimable que tant qu'il est « Nouveau ». */
    public function isEditable(): bool { return $this->status === ReportStatus::NEW; }

    /** Échéance théorique de traitement (polymorphisme via la catégorie). */
    public function dueDate(): ?\DateTimeImmutable
    {
        if ($this->createdAt === null) return null;
        return $this->createdAt->modify('+' . $this->category->processingDays() . ' days');
    }

    protected function hydrate(array $row): void
    {
        $this->id = isset($row['id']) ? (int) $row['id'] : null;
        $this->setTitle($row['title'] ?? '');
        $this->setDescription($row['description'] ?? '');
        $this->setAddress($row['address'] ?? '');
        $this->setPhoto($row['photo'] ?? null);
        $this->authorId = (int) ($row['author_id'] ?? 0);
        $this->agentId  = isset($row['agent_id']) ? (int) $row['agent_id'] : null;
        $this->setCategory($row['category'] ?? 'VOIRIE');
        $this->setStatus($row['status'] ?? ReportStatus::NEW->value);
        $this->authorName = $row['author_name'] ?? null;
        $this->setCreatedAt($row['created_at'] ?? null);
        $this->setUpdatedAt($row['updated_at'] ?? null);
    }
}
