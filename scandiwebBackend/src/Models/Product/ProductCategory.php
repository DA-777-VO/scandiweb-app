<?php

declare(strict_types=1);

namespace App\Models\Product;

enum ProductCategory: string
{
    case Clothes = 'clothes';
    case Tech    = 'tech';

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
