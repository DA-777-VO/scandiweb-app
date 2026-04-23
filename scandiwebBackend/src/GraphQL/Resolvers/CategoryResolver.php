<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\GraphQL\Queries\AllCategoriesQuery;
use App\GraphQL\Queries\CategoryByNameQuery;
use App\Models\Category\AbstractCategory;
use App\Repository\CategoryRepository;

class CategoryResolver
{
    private CategoryRepository $repository;

    public function __construct()
    {
        $this->repository = new CategoryRepository();
    }

    public function getAll(): array
    {
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $this->repository->find(new AllCategoriesQuery());

        return array_map(
            fn(array $row) => AbstractCategory::create($row)->toArray(),
            $rows
        );
    }

    /**
     * Returns null if the category is not found — correct GraphQL "not found" response.
     */
    public function getByName(string $name): ?array
    {
        /** @var array<string, mixed>|null $row */
        $row = $this->repository->find(new CategoryByNameQuery($name));

        return $row !== null ? AbstractCategory::create($row)->toArray() : null;
    }
}
