<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
use Throwable;

class Connection
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host     = $_ENV['DB_HOST']     ?? 'localhost';
            $port     = $_ENV['DB_PORT']     ?? '3306';
            $dbname   = $_ENV['DB_NAME']     ?? 'scandiweb';
            $user     = $_ENV['DB_USER']     ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';

            try {
                self::$instance = new PDO(
                    "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
                    $user,
                    $password,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Executes a callable inside a database transaction.
     *
     * All PDO operations in $block either ALL commit or ALL rollback.
     * Use wherever atomicity is required — removes begin/commit/rollback
     * boilerplate from every Repository.
     *
     * Example:
     *   $orderId = Connection::transaction(function () use ($pdo): int {
     *       $pdo->prepare('INSERT INTO orders ...')->execute();
     *       return (int) $pdo->lastInsertId();
     *   });
     *
     * @template T
     * @param  callable(): T $block
     * @return T  Whatever the callable returns
     * @throws Throwable  Re-throws any exception after rollback
     */
    public static function transaction(callable $block): mixed
    {
        $pdo = self::getInstance();
        $pdo->beginTransaction();

        try {
            $result = $block();
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function __construct() {}
    private function __clone() {}
}
