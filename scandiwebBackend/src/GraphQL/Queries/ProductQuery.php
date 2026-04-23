<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

/**
 * Marker interface for all product query strategies.
 * Each implementation encapsulates the parameters for one type of query.
 *
 * Usage:
 *   $query = new AllProductsQuery();
 *   $query = new ProductsByCategoryQuery(ProductCategory::Tech);
 *   $query = new ProductByIdQuery('ps-5');
 *
 *   $products = $repository->find($query);
 */
interface ProductQuery
{
}
