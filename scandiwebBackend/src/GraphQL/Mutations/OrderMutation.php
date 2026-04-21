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

    /**
     * Создаёт заказ и возвращает true.
     *
     * Возврат bool обусловлен GraphQL схемой (тип Boolean).
     * Логика: если createOrder() не бросил исключение — заказ создан → true.
     * При ошибке БД — createOrder() сам откатывает транзакцию и бросает
     * исключение, которое мы оборачиваем в UserError.
     *
     * UserError — это GraphQL-специфичное исключение: его message
     * попадает в поле errors[] ответа как читаемое сообщение для клиента,
     * а не как "Internal server error" (что случилось бы с обычным Exception).
     */
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
