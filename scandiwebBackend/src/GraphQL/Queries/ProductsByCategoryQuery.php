<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Product\ProductCategory;

/**
 * Query: return products filtered by category.
 */
final class ProductsByCategoryQuery implements ProductQuery
{
    public function __construct(
        public readonly ProductCategory $category
    ) {}
}
