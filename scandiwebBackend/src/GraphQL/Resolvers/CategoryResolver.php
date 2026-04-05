<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Database\Connection;
use App\Models\Category\GeneralCategory;

class CategoryResolver
{
    public static function getAll(): array
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->query('SELECT * FROM categories');
        $rows = $stmt->fetchAll();

        return array_map(
            fn(array $row) => (new GeneralCategory($row))->toArray(),
            $rows
        );
    }

    public static function getByName(string $name): ?array
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM categories WHERE name = ?');
        $stmt->execute([$name]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return (new GeneralCategory($row))->toArray();
    }
}
