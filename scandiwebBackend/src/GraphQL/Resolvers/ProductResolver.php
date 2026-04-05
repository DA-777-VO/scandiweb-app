<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Database\Connection;
use App\Models\Product\AbstractProduct;
use App\Models\Product\ProductFactory;

class ProductResolver
{
    public static function getAll(?string $category = null): array
    {
        $pdo = Connection::getInstance();

        if ($category && $category !== 'all') {
            $stmt = $pdo->prepare('SELECT * FROM products WHERE category = ?');
            $stmt->execute([$category]);
        } else {
            $stmt = $pdo->query('SELECT * FROM products');
        }

        $rows = $stmt->fetchAll();

        return array_map(
            fn(array $row) => self::hydrateProduct($row)->toArray(),
            $rows
        );
    }

    public static function getById(string $id): ?array
    {
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return self::hydrateProduct($row)->toArray();
    }

    private static function hydrateProduct(array $row): AbstractProduct
    {
        $product = ProductFactory::create($row);

        // Load attributes
        $pdo = Connection::getInstance();
        $stmt = $pdo->prepare(
            'SELECT a.*, ai.id as item_id, ai.display_value, ai.value as item_value
             FROM attributes a
             LEFT JOIN attribute_items ai ON ai.attribute_id = a.id AND ai.product_id = a.product_id
             WHERE a.product_id = ?
             ORDER BY a.id, ai.sort_order'
        );
        $stmt->execute([$row['id']]);
        $attrRows = $stmt->fetchAll();

        $attributesMap = [];
        foreach ($attrRows as $attrRow) {
            $attrKey = $attrRow['id'] . '_' . $attrRow['product_id'];
            if (!isset($attributesMap[$attrKey])) {
                $attributesMap[$attrKey] = [
                    'id' => $attrRow['name'],
                    'name' => $attrRow['name'],
                    'type' => $attrRow['type'],
                    'items' => [],
                ];
            }
            if ($attrRow['item_id']) {
                $attributesMap[$attrKey]['items'][] = [
                    'id' => $attrRow['item_id'],
                    'displayValue' => $attrRow['display_value'],
                    'value' => $attrRow['item_value'],
                ];
            }
        }

        $product->setAttributes(array_values($attributesMap));

        // Load prices
        $stmt = $pdo->prepare(
            'SELECT p.amount, c.label, c.symbol
             FROM prices p
             JOIN currencies c ON c.id = p.currency_id
             WHERE p.product_id = ?'
        );
        $stmt->execute([$row['id']]);
        $priceRows = $stmt->fetchAll();

        $prices = array_map(fn(array $pr) => [
            'amount' => (float) $pr['amount'],
            'currency' => [
                'label' => $pr['label'],
                'symbol' => $pr['symbol'],
            ],
        ], $priceRows);

        $product->setPrices($prices);

        return $product;
    }
}
