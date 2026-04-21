<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Connection;
use Throwable;

class OrderRepository
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance();
    }

    /**
     * Creates order with items inside a transaction.
     * Returns the new order id.
     *
     * @param array<int, array<string, mixed>> $items
     */
    public function createOrder(array $items): int
    {
        $orderId = 0;

        $this->atomicTransaction(function () use ($items, &$orderId) {
            $stmt = $this->pdo->prepare('INSERT INTO orders (created_at) VALUES (NOW())');
            $stmt->execute();
            $orderId = (int)$this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, quantity, selected_attributes)
             VALUES (?, ?, ?, ?)'
            );

            // TODO use batch insert
            foreach ($items as $item) {
                $stmt->execute([
                    $orderId,
                    $item['productId'],
                    $item['quantity'],
                    $item['selectedAttributes'] ?? null,
                ]);
            }
        });

        return $orderId;
    }

    // TODO USE THIS THING AND MAKE STATIC OF MOVE TO DB_HELPER_MODULE
    public function atomicTransaction(callable $block): void
    {
        $this->pdo->beginTransaction();
        try {
            $block();
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}


