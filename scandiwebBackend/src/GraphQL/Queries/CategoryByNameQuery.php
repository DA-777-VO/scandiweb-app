<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

final class CategoryByNameQuery implements CategoryQuery
{
    public function __construct(
        public readonly string $name
    ) {}
}
