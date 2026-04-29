<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Repository\OrderRepository;
use GraphQL\Error\UserError;

class OrderMutation
{
    private OrderRepository $repository;

    public function __construct()
    {
        $this->repository = new OrderRepository();
    }

    public function placeOrder(array $items): bool
    {
        try {
            $this->repository->createOrder($items);
            return true;
        } catch (\Throwable $e) {
            throw new UserError('Failed to place order: ' . $e->getMessage());
        }
    }
}
