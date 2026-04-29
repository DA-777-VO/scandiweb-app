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
        $rows = $this->repository->find(new AllCategoriesQuery());

        return array_map(
            fn(array $row) => AbstractCategory::create($row)->toArray(),
            $rows
        );
    }

    public function getByName(string $name): ?array
    {
        $row = $this->repository->find(new CategoryByNameQuery($name));

        return $row !== null ? AbstractCategory::create($row)->toArray() : null;
    }
}
