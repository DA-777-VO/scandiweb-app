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

    /**
     * Creates an order with all its items atomically.
     * Uses Connection::transaction() — all inserts commit or all rollback.
     * prepare() called once, execute() in loop — correct prepared statement pattern.
     *
     * @param  array<int, array<string, mixed>> $items
     * @return int  The new order id
     * @throws \Throwable on any DB error (transaction rolled back automatically)
     */
    public function createOrder(array $items): int
    {
        return Connection::transaction(function () use ($items): int {
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

            return $orderId;
        });
    }
}
