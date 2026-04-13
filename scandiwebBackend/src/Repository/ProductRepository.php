<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Connection;

class ProductRepository
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance();
    }

    public function findAll(?string $category = null): array
    {
        if ($category !== null && $category !== 'all') {
            $stmt = $this->pdo->prepare('SELECT * FROM products WHERE category = ?');
            $stmt->execute([$category]);
        } else {
            $stmt = $this->pdo->query('SELECT * FROM products');
        }

        return $stmt->fetchAll();
    }
    public function findById(string $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    public function findAttributesByProductId(string $productId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.*, ai.id as item_id, ai.display_value, ai.value as item_value
             FROM attributes a
             LEFT JOIN attribute_items ai
                 ON ai.attribute_id = a.id
                 AND ai.product_id  = a.product_id
             WHERE a.product_id = ?
             ORDER BY a.id, ai.sort_order'
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function findPricesByProductId(string $productId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.amount, c.label, c.symbol
             FROM prices p
             JOIN currencies c ON c.id = p.currency_id
             WHERE p.product_id = ?'
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
}
