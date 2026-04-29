<?php

declare(strict_types=1);

namespace App\Models\Product;

final class TechProduct extends AbstractProduct
{
    public function getCategory(): ProductCategory
    {
        return ProductCategory::Tech;
    }
}
