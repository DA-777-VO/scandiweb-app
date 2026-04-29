<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

final class ProductByIdQuery implements ProductQuery
{
    public function __construct(
        public readonly string $id
    ) {}
}
