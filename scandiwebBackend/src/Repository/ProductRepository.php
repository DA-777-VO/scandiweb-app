<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Connection;
use App\Models\Product\ProductCategory;

class ProductRepository
{
    /**
     * Максимальное количество id в одном IN(...) запросе.
     * Предотвращает слишком длинные запросы при большом количестве продуктов.
     * При превышении — запросы разбиваются на чанки и результаты сливаются.
     */
    private const CHUNK_SIZE = 100;

    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance();
    }

    // ── Продукты ──────────────────────────────────────────────────────────────

    /**
     * Возвращает продукты, опционально отфильтрованные по категории.
     * Принимает enum — никаких строковых проверок внутри.
     * ProductCategory::All означает "без фильтра".
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAll(ProductCategory $category): array
    {
        if ($category->isAll()) {
            $stmt = $this->pdo->query('SELECT * FROM products');
        } else {
            $stmt = $this->pdo->prepare('SELECT * FROM products WHERE category = ?');
            $stmt->execute([$category->value]);
        }

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findById(string $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    // ── Batch + Chunking: атрибуты ────────────────────────────────────────────

    /**
     * Загружает атрибуты для списка product_id.
     * При большом списке разбивает на чанки по CHUNK_SIZE,
     * чтобы не генерировать слишком длинные IN(...) выражения.
     *
     * @param  non-empty-array<string> $productIds
     * @return array<string, array<int, array<string, mixed>>>  keyed by product_id
     * @throws \InvalidArgumentException если передан пустой массив
     */
    public function findAttributesByProductIds(array $productIds): array
    {
        if (empty($productIds)) {
            throw new \InvalidArgumentException(
                'findAttributesByProductIds requires at least one product id.'
            );
        }

        $chunks = array_chunk($productIds, self::CHUNK_SIZE);
        $result = [];

        foreach ($chunks as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '?'));

            $stmt = $this->pdo->prepare(
                "SELECT
                    a.id          AS attr_row_id,
                    a.product_id,
                    a.name        AS attr_name,
                    a.type        AS attr_type,
                    ai.id         AS item_id,
                    ai.display_value,
                    ai.value      AS item_value,
                    ai.sort_order
                 FROM attributes a
                 LEFT JOIN attribute_items ai
                     ON ai.attribute_id = a.id
                     AND ai.product_id  = a.product_id
                 WHERE a.product_id IN ({$placeholders})
                 ORDER BY a.product_id, a.id, ai.sort_order"
            );
            $stmt->execute($chunk);

            // Сливаем результаты чанков в общий результат
            $result = array_merge_recursive(
                $result,
                $this->groupAttributeRows($stmt->fetchAll())
            );
        }

        return $result;
    }

    // ── Batch + Chunking: цены ────────────────────────────────────────────────

    /**
     * Загружает цены для списка product_id.
     * При большом списке разбивает на чанки по CHUNK_SIZE.
     *
     * @param  non-empty-array<string> $productIds
     * @return array<string, array<int, array<string, mixed>>>  keyed by product_id
     * @throws \InvalidArgumentException если передан пустой массив
     */
    public function findPricesByProductIds(array $productIds): array
    {
        if (empty($productIds)) {
            throw new \InvalidArgumentException(
                'findPricesByProductIds requires at least one product id.'
            );
        }

        $chunks = array_chunk($productIds, self::CHUNK_SIZE);
        $result = [];

        foreach ($chunks as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '?'));

            $stmt = $this->pdo->prepare(
                "SELECT
                    p.product_id,
                    p.amount,
                    c.label  AS currency_label,
                    c.symbol AS currency_symbol
                 FROM prices p
                 JOIN currencies c ON c.id = p.currency_id
                 WHERE p.product_id IN ({$placeholders})"
            );
            $stmt->execute($chunk);

            $result = array_merge(
                $result,
                $this->groupPriceRows($stmt->fetchAll())
            );
        }

        return $result;
    }

    // ── Приватные методы группировки ──────────────────────────────────────────

    /** @return array<string, array<int, array<string, mixed>>> */
    private function groupAttributeRows(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $pid    = $row['product_id'];
            $attrId = $row['attr_row_id'];

            if (!isset($grouped[$pid][$attrId])) {
                $grouped[$pid][$attrId] = [
                    'id'    => $row['attr_name'],
                    'name'  => $row['attr_name'],
                    'type'  => $row['attr_type'],
                    'items' => [],
                ];
            }

            if ($row['item_id'] !== null) {
                $grouped[$pid][$attrId]['items'][] = [
                    'id'           => $row['item_id'],
                    'displayValue' => $row['display_value'],
                    'value'        => $row['item_value'],
                ];
            }
        }

        return array_map(
            fn(array $attrs) => array_values($attrs),
            $grouped
        );
    }

    /** @return array<string, array<int, array<string, mixed>>> */
    private function groupPriceRows(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $grouped[$row['product_id']][] = [
                'amount'   => (float) $row['amount'],
                'currency' => [
                    'label'  => $row['currency_label'],
                    'symbol' => $row['currency_symbol'],
                ],
            ];
        }

        return $grouped;
    }
}
