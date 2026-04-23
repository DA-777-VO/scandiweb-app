<?php

declare(strict_types=1);

namespace App\Models\Product;

/**
 * Product of category "tech".
 * Category is declared here as part of the type — not stored as runtime data.
 */
final class TechProduct extends AbstractProduct
{
    public function getCategory(): ProductCategory
    {
        return ProductCategory::Tech;
    }
}
