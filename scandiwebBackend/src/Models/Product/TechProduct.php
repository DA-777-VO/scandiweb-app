<?php

declare(strict_types=1);

namespace App\Models\Product;

class TechProduct extends AbstractProduct
{
    public function toArray(): array
    {
        return $this->baseToArray();
    }
}
