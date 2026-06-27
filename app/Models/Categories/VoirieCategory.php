<?php
declare(strict_types=1);
namespace App\Models\Categories;

final class VoirieCategory extends AbstractCategory
{
    public function code(): string { return 'VOIRIE'; }
    public function label(): string { return 'Voirie'; }
    public function processingDays(): int { return 7; }
    public function defaultPriority(): int { return 2; }
    public function icon(): string { return 'bi-cone-striped'; }
}
