<?php

declare(strict_types=1);

namespace App\Models\Attribute;

final class SwatchAttribute extends AbstractAttribute
{
    public function getType(): AttributeType
    {
        return AttributeType::Swatch;
    }
}
