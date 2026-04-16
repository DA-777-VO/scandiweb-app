<?php

declare(strict_types=1);

namespace App\Models\Attribute;

/**
 * Backed enum для типов атрибутов.
 */
enum AttributeType: string
{
    case Text   = 'text';
    case Swatch = 'swatch';

    /**
     * @throws \InvalidArgumentException для неизвестных типов
     */
    public static function fromString(string $value): self
    {
        $case = self::tryFrom($value);

        if ($case === null) {
            throw new \InvalidArgumentException(
                "Unknown attribute type: '{$value}'. Valid values: "
                . implode(', ', array_column(self::cases(), 'value'))
            );
        }

        return $case;
    }
}
