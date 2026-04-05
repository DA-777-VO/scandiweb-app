<?php

declare(strict_types=1);

namespace App\Models\Category;

class GeneralCategory extends AbstractCategory
{
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
