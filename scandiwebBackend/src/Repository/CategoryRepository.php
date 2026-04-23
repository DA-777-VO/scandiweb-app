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

    /**
     * Dispatches to the correct SQL query based on the CategoryQuery strategy object.
     *
     * @return array<int, array<string, mixed>>|array<string, mixed>|null
     * @throws \InvalidArgumentException for unknown query types
     */
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

    // ── Private SQL methods ───────────────────────────────────────────────────

    /** @return array<int, array<string, mixed>> */
    private function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM categories');
        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    private function findByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE name = ?');
        $stmt->execute([$name]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }
}
