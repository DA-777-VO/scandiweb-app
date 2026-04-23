<?php

declare(strict_types=1);

namespace App\Models\Product;

/**
 * Backed enum for product categories stored in the database.
 * Does NOT contain "All" — "all" is a filter concept handled by query objects,
 * not a category a product can belong to.
 */
enum ProductCategory: string
{
    case Clothes = 'clothes';
    case Tech    = 'tech';

    /**
     * @throws \InvalidArgumentException for unknown values
     */
    public static function fromStringOrThrow(string $value): self
    {
        $case = self::tryFrom($value);

        if ($case === null) {
            throw new \InvalidArgumentException(
                "Unknown product category: '{$value}'. Valid values: "
                . implode(', ', array_column(self::cases(), 'value'))
            );
        }

        return $case;
    }
}
