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
            self::$instance = self::createConnection();
        }

        return self::$instance;
    }

    /**
     * Executes a callable inside a database transaction.
     * All operations in $block either ALL commit or ALL rollback.
     *
     * @template T
     * @param  callable(): T $block
     * @return T
     * @throws Throwable re-throws after rollback
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

    /**
     * Creates the PDO connection.
     * Crashes immediately if any required env variable is missing.
     *
     * @throws \RuntimeException
     */
    private static function createConnection(): PDO
    {
        $host     = self::requireEnv('DB_HOST');
        $port     = self::requireEnv('DB_PORT');
        $dbname   = self::requireEnv('DB_NAME');
        $user     = self::requireEnv('DB_USER');
        // DB_PASSWORD is allowed to be empty — standard local MySQL setup has no password
        $password = self::optionalEnv('DB_PASSWORD');

        try {
            return new PDO(
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
            throw new \RuntimeException(
                "Database connection failed: {$e->getMessage()}. "
                . "Check that MySQL is running and credentials in .env are correct."
            );
        }
    }

    /**
     * Reads a required environment variable.
     * Crashes if the variable is not set or is empty.
     *
     * @throws \RuntimeException
     */
    private static function requireEnv(string $name): string
    {
        $value = $_ENV[$name] ?? null;

        if ($value === null || $value === '') {
            throw new \RuntimeException(
                "Required environment variable '{$name}' is not set. "
                . "Add it to your .env file."
            );
        }

        return $value;
    }

    /**
     * Reads an optional environment variable.
     * Returns empty string if the variable is not set — used for DB_PASSWORD
     * which is legitimately empty in standard local MySQL installations.
     */
    private static function optionalEnv(string $name): string
    {
        return $_ENV[$name] ?? '';
    }

    private function __construct() {}
    private function __clone() {}
}
