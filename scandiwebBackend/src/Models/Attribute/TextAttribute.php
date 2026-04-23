<?php

declare(strict_types=1);

namespace App\Models\Attribute;

/**
 * Text-based attribute (Size, Capacity, With USB 3 ports, etc.).
 * Type is declared here — not stored as runtime data.
 */
final class TextAttribute extends AbstractAttribute
{
    public function getType(): AttributeType
    {
        return AttributeType::Text;
    }
}
