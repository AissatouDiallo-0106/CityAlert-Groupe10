<?php
declare(strict_types=1);
namespace App\Models\Categories;

final class DechetsCategory extends AbstractCategory
{
    public function code(): string { return 'DECHETS'; }
    public function label(): string { return 'Déchets'; }
    public function processingDays(): int { return 3; }
    public function defaultPriority(): int { return 1; }
    public function icon(): string { return 'bi-trash'; }
}
