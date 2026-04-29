<?php

declare(strict_types=1);

namespace App\Models\Product;

final class ClothesProduct extends AbstractProduct
{
    public function getCategory(): ProductCategory
    {
        return ProductCategory::Clothes;
    }
}
