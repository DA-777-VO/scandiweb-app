<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Database\Connection;

class OrderMutation
{
    public static function placeOrder(array $items): bool
    {
        $pdo = Connection::getInstance();

        try {
            $pdo->beginTransaction();

            // Create order
            $stmt = $pdo->prepare('INSERT INTO orders (created_at) VALUES (NOW())');
            $stmt->execute();
            $orderId = (int) $pdo->lastInsertId();

            // Create order items
            $stmt = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, quantity, selected_attributes) VALUES (?, ?, ?, ?)'
            );

            foreach ($items as $item) {
                $stmt->execute([
                    $orderId,
                    $item['productId'],
                    $item['quantity'],
                    $item['selectedAttributes'] ?? null,
                ]);
            }

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
