<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Connection;
use App\GraphQL\Queries\AllCategoriesQuery;
use App\GraphQL\Queries\CategoryByNameQuery;
use App\GraphQL\Queries\CategoryQuery;

class CategoryRepository
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance();
    }

    public function find(CategoryQuery $query): array|null
    {
        return match (true) {
            $query instanceof AllCategoriesQuery  => $this->findAll(),
            $query instanceof CategoryByNameQuery => $this->findByName($query->name),
            default => throw new \InvalidArgumentException(
                'Unknown CategoryQuery type: ' . $query::class
            ),
        };
    }

    private function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM categories');
        return $stmt->fetchAll();
    }

    private function findByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE name = ?');
        $stmt->execute([$name]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }
}
