<?php

declare(strict_types=1);

namespace App\Models\Product;

/**
 * Backed enum для категорий продуктов.
 * Единственный источник правды о допустимых значениях категории.
 * Устраняет строковые проверки вида !== 'all', !== null по всему коду.
 */
enum ProductCategory: string
{
    case All     = 'all';
    case Clothes = 'clothes';
    case Tech    = 'tech';

    /**
     * Конвертирует nullable строку из GraphQL аргумента в enum.
     * null (аргумент не передан) → All (без фильтра).
     *
     * @throws \InvalidArgumentException для неизвестных значений
     */
    public static function fromNullableString(?string $value): self
    {
        if ($value === null) {
            return self::All;
        }

        return self::fromString($value);
    }

    /**
     * Конвертирует строку из БД в enum.
     *
     * @throws \InvalidArgumentException для неизвестных значений
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

    /**
     * Означает ли этот кейс "без фильтра по категории".
     */
    public function isAll(): bool
    {
        return $this === self::All;
    }
}
