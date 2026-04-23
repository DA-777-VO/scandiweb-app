<?php

declare(strict_types=1);

namespace App\Models\Product;

/**
 * Product of category "clothes".
 * Category is declared here as part of the type — not stored as runtime data.
 */
final class ClothesProduct extends AbstractProduct
{
    public function getCategory(): ProductCategory
    {
        return ProductCategory::Clothes;
    }
}
