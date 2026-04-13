<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Connection;

class OrderRepository
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance();
    }

    public function createOrder(array $items): int
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare('INSERT INTO orders (created_at) VALUES (NOW())');
            $stmt->execute();
            $orderId = (int) $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, quantity, selected_attributes)
                 VALUES (?, ?, ?, ?)'
            );

            foreach ($items as $item) {
                $stmt->execute([
                    $orderId,
                    $item['productId'],
                    $item['quantity'],
                    $item['selectedAttributes'] ?? null,
                ]);
            }

            $this->pdo->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
