<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

/**
 * Query: return a single category by name.
 */
final class CategoryByNameQuery implements CategoryQuery
{
    public function __construct(
        public readonly string $name
    ) {}
}
