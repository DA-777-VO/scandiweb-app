<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Connection;
use App\GraphQL\Queries\AllProductsQuery;
use App\GraphQL\Queries\ProductByIdQuery;
use App\GraphQL\Queries\ProductQuery;
use App\GraphQL\Queries\ProductsByCategoryQuery;

class ProductRepository
{
    private const CHUNK_SIZE = 100;

    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance();
    }

    // ── Strategy: single entry point ─────────────────────────────────────────

    /**
     * @return array<int, array<string, mixed>>|array<string, mixed>|null
     * @throws \InvalidArgumentException for unknown query types
     */
    public function find(ProductQuery $query): array|null
    {
        return match (true) {
            $query instanceof AllProductsQuery        => $this->findAll(),
            $query instanceof ProductsByCategoryQuery => $this->findByCategory($query->category->value),
            $query instanceof ProductByIdQuery        => $this->findById($query->id),
            default => throw new \InvalidArgumentException(
                'Unknown ProductQuery type: ' . $query::class
            ),
        };
    }

    // ── Private SQL: products ─────────────────────────────────────────────────

    /** @return array<int, array<string, mixed>> */
    private function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM products');
        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    private function findByCategory(string $category): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE category = ?');
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    private function findById(string $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    // ── Batch: gallery (from product_gallery table) ───────────────────────────

    /**
     * Loads gallery URLs for a list of product ids — one query for all.
     * gallery was moved from a JSON column to a normalised table.
     *
     * @param  non-empty-array<string> $productIds
     * @return array<string, string[]>  [product_id => [url, url, ...]]
     * @throws \InvalidArgumentException on empty input
     */
    public function findGalleryByProductIds(array $productIds): array
    {
        if (empty($productIds)) {
            throw new \InvalidArgumentException(
                'findGalleryByProductIds requires at least one product id.'
            );
        }

        $result = [];

        foreach (array_chunk($productIds, self::CHUNK_SIZE) as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '?'));

            $stmt = $this->pdo->prepare(
                "SELECT product_id, url
                 FROM product_gallery
                 WHERE product_id IN ({$placeholders})
                 ORDER BY product_id, sort_order"
            );
            $stmt->execute($chunk);

            foreach ($stmt->fetchAll() as $row) {
                $result[$row['product_id']][] = $row['url'];
            }
        }

        return $result;
    }

    // ── Batch: attributes ─────────────────────────────────────────────────────

    /**
     * @param  non-empty-array<string> $productIds
     * @return array<string, array<int, array<string, mixed>>>
     * @throws \InvalidArgumentException on empty input
     */
    public function findAttributesByProductIds(array $productIds): array
    {
        if (empty($productIds)) {
            throw new \InvalidArgumentException(
                'findAttributesByProductIds requires at least one product id.'
            );
        }

        $result = [];

        foreach (array_chunk($productIds, self::CHUNK_SIZE) as $chunk) {
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

            $result = array_merge_recursive(
                $result,
                $this->groupAttributeRows($stmt->fetchAll())
            );
        }

        return $result;
    }

    // ── Batch: prices ─────────────────────────────────────────────────────────

    /**
     * @param  non-empty-array<string> $productIds
     * @return array<string, array<int, array<string, mixed>>>
     * @throws \InvalidArgumentException on empty input
     */
    public function findPricesByProductIds(array $productIds): array
    {
        if (empty($productIds)) {
            throw new \InvalidArgumentException(
                'findPricesByProductIds requires at least one product id.'
            );
        }

        $result = [];

        foreach (array_chunk($productIds, self::CHUNK_SIZE) as $chunk) {
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

    // ── Grouping helpers ──────────────────────────────────────────────────────

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

        return array_map(fn(array $attrs) => array_values($attrs), $grouped);
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
