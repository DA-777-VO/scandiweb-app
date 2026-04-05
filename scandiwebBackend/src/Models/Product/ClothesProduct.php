<?php

declare(strict_types=1);

namespace App\Models\Product;

class ClothesProduct extends AbstractProduct
{
    public function toArray(): array
    {
        return $this->baseToArray();
    }
}
