<?php

namespace Tests\Unit\Repository;

use App\Database\Connection;
use App\Repository\OrderRepository;
use PHPUnit\Framework\TestCase;

class OrderRepositoryTest extends TestCase
{
    private \PDO $pdo;
    private ?\PDO $previousConnection = null;

    protected function setUp(): void
    {
        $this->previousConnection = $this->getConnectionInstance();
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->pdo->sqliteCreateFunction('NOW', fn(): string => '2026-04-29 00:00:00');
        $this->createSchema();
        $this->setConnectionInstance($this->pdo);
    }

    protected function tearDown(): void
    {
        $this->setConnectionInstance($this->previousConnection);
    }

    public function testCreateOrderPersistsOrderAndItemsAtomically(): void
    {
        $repository = $this->createRepository($this->pdo);

        $orderId = $repository->createOrder([
            [
                'productId' => 'apollo-shirt',
                'quantity' => 2,
                'selectedAttributes' => '{"Size":"M","Color":"#44ff03"}',
            ],
            [
                'productId' => 'apollo-laptop',
                'quantity' => 1,
                'selectedAttributes' => null,
            ],
        ]);

        $orders = $this->pdo->query('SELECT * FROM orders')->fetchAll();
        $items = $this->pdo
            ->query('SELECT product_id, quantity, selected_attributes FROM order_items ORDER BY id')
            ->fetchAll();

        $this->assertSame(1, $orderId);
        $this->assertCount(1, $orders);
        $this->assertSame('2026-04-29 00:00:00', $orders[0]['created_at']);
        $this->assertSame(
            [
                [
                    'product_id' => 'apollo-shirt',
                    'quantity' => 2,
                    'selected_attributes' => '{"Size":"M","Color":"#44ff03"}',
                ],
                [
                    'product_id' => 'apollo-laptop',
                    'quantity' => 1,
                    'selected_attributes' => null,
                ],
            ],
            $items
        );
    }

    public function testCreateOrderRollsBackWhenAnyItemInsertFails(): void
    {
        $repository = $this->createRepository($this->pdo);

        try {
            $repository->createOrder([
                [
                    'productId' => 'apollo-shirt',
                    'quantity' => 1,
                    'selectedAttributes' => '{}',
                ],
                [
                    'productId' => null,
                    'quantity' => 1,
                    'selectedAttributes' => '{}',
                ],
            ]);
            $this->fail('Expected order creation to fail.');
        } catch (\Throwable $e) {
            $this->assertStringContainsString('NOT NULL', $e->getMessage());
        }

        $orderCount = (int) $this->pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
        $itemCount = (int) $this->pdo->query('SELECT COUNT(*) FROM order_items')->fetchColumn();

        $this->assertSame(0, $orderCount);
        $this->assertSame(0, $itemCount);
    }

    private function createRepository(\PDO $pdo): OrderRepository
    {
        $reflection = new \ReflectionClass(OrderRepository::class);
        $repository = $reflection->newInstanceWithoutConstructor();
        $property = $reflection->getProperty('pdo');
        $property->setAccessible(true);
        $property->setValue($repository, $pdo);

        return $repository;
    }

    private function createSchema(): void
    {
        $this->pdo->exec('
            CREATE TABLE orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                created_at TEXT NOT NULL
            )
        ');
        $this->pdo->exec('
            CREATE TABLE order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id TEXT NOT NULL,
                quantity INTEGER NOT NULL,
                selected_attributes TEXT
            )
        ');
    }

    private function getConnectionInstance(): ?\PDO
    {
        $property = new \ReflectionProperty(Connection::class, 'instance');
        $property->setAccessible(true);

        return $property->getValue();
    }

    private function setConnectionInstance(?\PDO $pdo): void
    {
        $property = new \ReflectionProperty(Connection::class, 'instance');
        $property->setAccessible(true);
        $property->setValue(null, $pdo);
    }
}
