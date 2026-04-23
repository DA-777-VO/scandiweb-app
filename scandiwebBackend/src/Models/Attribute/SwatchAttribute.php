<?php

declare(strict_types=1);

namespace App\Models\Attribute;

/**
 * Color swatch attribute (Color).
 * value contains a HEX color code (#44FF03).
 * Type is declared here — not stored as runtime data.
 */
final class SwatchAttribute extends AbstractAttribute
{
    public function getType(): AttributeType
    {
        return AttributeType::Swatch;
    }
}
