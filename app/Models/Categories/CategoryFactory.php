<?php
declare(strict_types=1);
namespace App\Models\Categories;

/**
 * Fabrique de catégories : code -> instance concrète (polymorphisme).
 */
final class CategoryFactory
{
    private const MAP = [
        'VOIRIE'    => VoirieCategory::class,
        'ECLAIRAGE' => EclairageCategory::class,
        'DECHETS'   => DechetsCategory::class,
        'EAU'       => EauCategory::class,
    ];

    public static function make(string $code): AbstractCategory
    {
        $class = self::MAP[strtoupper($code)] ?? VoirieCategory::class;
        return new $class();
    }

    /** @return list<AbstractCategory> Toutes les catégories disponibles. */
    public static function all(): array
    {
        return array_map(static fn(string $c) => new $c(), array_values(self::MAP));
    }
}
