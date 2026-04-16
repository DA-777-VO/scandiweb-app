<?php

declare(strict_types=1);

namespace App\Models\Product;

/**
 * Backed enum для категорий продуктов.
 * Гарантирует что в систему попадают только известные значения категорий.
 * Заменяет строковые литералы в match() и передачу category как string.
 */
enum ProductCategory: string
{
    case Clothes = 'clothes';
    case Tech    = 'tech';

    /**
     * Создаёт enum из строки (например из БД).
     * Бросает InvalidArgumentException вместо пустого default.
     *
     * @throws \InvalidArgumentException для неизвестных категорий
     */
    public static function fromString(string $value): self
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
