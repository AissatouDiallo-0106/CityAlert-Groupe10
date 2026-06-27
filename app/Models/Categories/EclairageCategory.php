<?php
declare(strict_types=1);
namespace App\Models\Categories;

final class EclairageCategory extends AbstractCategory
{
    public function code(): string { return 'ECLAIRAGE'; }
    public function label(): string { return 'Éclairage'; }
    public function processingDays(): int { return 5; }
    public function defaultPriority(): int { return 2; }
    public function icon(): string { return 'bi-lightbulb'; }
}
