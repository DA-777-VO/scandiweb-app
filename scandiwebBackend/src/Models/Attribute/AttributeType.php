<?php

declare(strict_types=1);

namespace App\Models\Attribute;

enum AttributeType: string
{
    case Text   = 'text';
    case Swatch = 'swatch';

    /**
     * @throws \InvalidArgumentException for unknown values
     */
    public static function fromStringOrThrow(string $value): self
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
