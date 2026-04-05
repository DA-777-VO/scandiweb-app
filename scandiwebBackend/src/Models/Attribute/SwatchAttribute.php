<?php

declare(strict_types=1);

namespace App\Models\Attribute;

class SwatchAttribute extends AbstractAttribute
{
    public function formatItems(): array
    {
        return array_map(function (array $item) {
            return [
                'id' => $item['id'],
                'displayValue' => $item['displayValue'],
                'value' => $item['value'],
            ];
        }, $this->items);
    }
}
