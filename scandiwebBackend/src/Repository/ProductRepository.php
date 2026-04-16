<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Connection;

class ProductRepository
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance();
    }

    // ── Продукты ──────────────────────────────────────────────────────────────

    /** @return array<int, array<string, mixed>> */
    public function findAll(?string $category = null): array
    {
        if ($category !== null && $category !== 'all') {
            $stmt = $this->pdo->prepare('SELECT * FROM products WHERE category = ?');
            $stmt->execute([$category]);
        } else {
            $stmt = $this->pdo->query('SELECT * FROM products');
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

    // ── Batch: атрибуты для СПИСКА продуктов (один запрос вместо N) ──────────

    /**
     * Загружает атрибуты для всех переданных product_id одним SQL запросом.
     *
     * Решает N+1 Problem:
     *   До: для 8 продуктов = 8 запросов атрибутов + 8 запросов цен = 17 запросов
     *   После: 1 запрос атрибутов + 1 запрос цен = 3 запроса на весь список
     *
     * @param  string[] $productIds
     * @return array<string, array<int, array<string, mixed>>>  keyed by product_id
     */
    public function findAttributesByProductIds(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

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
        $stmt->execute($productIds);

        return $this->groupAttributeRows($stmt->fetchAll());
    }

    // ── Batch: цены для СПИСКА продуктов (один запрос вместо N) ─────────────

    /**
     * Загружает цены для всех переданных product_id одним SQL запросом.
     *
     * @param  string[] $productIds
     * @return array<string, array<int, array<string, mixed>>>  keyed by product_id
     */
    public function findPricesByProductIds(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

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
        $stmt->execute($productIds);

        return $this->groupPriceRows($stmt->fetchAll());
    }

    // ── Приватные методы группировки ──────────────────────────────────────────

    /**
     * Группирует плоские JOIN-строки в структуру:
     * [
     *   'ps-5' => [
     *     ['id'=>'Color', 'name'=>'Color', 'type'=>'swatch', 'items'=>[...]],
     *     ['id'=>'Capacity', 'name'=>'Capacity', 'type'=>'text', 'items'=>[...]],
     *   ],
     *   ...
     * ]
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function groupAttributeRows(array $rows): array
    {
        // [product_id => [attr_row_id => attr_data]]
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

        // Переиндексируем вложенные массивы (убираем int ключи attr_row_id)
        return array_map(
            fn(array $attrs) => array_values($attrs),
            $grouped
        );
    }

    /**
     * Группирует строки цен:
     * [
     *   'ps-5' => [
     *     ['amount' => 844.02, 'currency' => ['label' => 'USD', 'symbol' => '$']],
     *   ],
     * ]
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
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
