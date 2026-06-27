<?php
declare(strict_types=1);
namespace App\Models\Entities;

use App\Enums\ReportStatus;

final class StatusHistory extends AbstractEntity
{
    private int $reportId = 0;
    private ?int $agentId = null;
    private ReportStatus $status = ReportStatus::NEW;
    private ?string $comment = null;
    private ?string $agentName = null;

    public function getReportId(): int { return $this->reportId; }
    public function setReportId(int $v): void { $this->reportId = $v; }
    public function getAgentId(): ?int { return $this->agentId; }
    public function setAgentId(?int $v): void { $this->agentId = $v; }
    public function getStatus(): ReportStatus { return $this->status; }
    public function setStatus(ReportStatus|string $v): void { $this->status = $v instanceof ReportStatus ? $v : ReportStatus::from($v); }
    public function getComment(): ?string { return $this->comment; }
    public function setComment(?string $v): void { $this->comment = $v ?: null; }
    public function getAgentName(): ?string { return $this->agentName; }

    protected function hydrate(array $row): void
    {
        $this->id = isset($row['id']) ? (int) $row['id'] : null;
        $this->reportId = (int) ($row['report_id'] ?? 0);
        $this->agentId  = isset($row['agent_id']) ? (int) $row['agent_id'] : null;
        $this->setStatus($row['status'] ?? ReportStatus::NEW->value);
        $this->setComment($row['comment'] ?? null);
        $this->agentName = $row['agent_name'] ?? null;
        $this->setCreatedAt($row['created_at'] ?? null);
    }
}
