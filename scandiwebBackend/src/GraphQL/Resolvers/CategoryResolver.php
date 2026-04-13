<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Models\Category\GeneralCategory;
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
        $rows = $this->repository->findAll();

        return array_map(
            fn(array $row) => (new GeneralCategory($row))->toArray(),
            $rows
        );
    }

    public function getByName(string $name): ?array
    {
        $row = $this->repository->findByName($name);

        if ($row === null) {
            return null;
        }

        return (new GeneralCategory($row))->toArray();
    }
}
