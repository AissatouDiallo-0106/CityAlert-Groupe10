<?php
declare(strict_types=1);
namespace App\Models\Categories;

final class EauCategory extends AbstractCategory
{
    public function code(): string { return 'EAU'; }
    public function label(): string { return 'Eau et assainissement'; }
    public function processingDays(): int { return 2; }
    public function defaultPriority(): int { return 1; }
    public function icon(): string { return 'bi-droplet'; }
}
