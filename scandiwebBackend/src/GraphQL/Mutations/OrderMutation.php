<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Repository\OrderRepository;

class OrderMutation
{
    private OrderRepository $repository;

    public function __construct()
    {
        $this->repository = new OrderRepository();
    }

    public function placeOrder(array $items): bool
    {
        $this->repository->createOrder($items);
        return true;
    }
}
