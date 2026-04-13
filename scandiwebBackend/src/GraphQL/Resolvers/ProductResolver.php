<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Models\Product\AbstractProduct;
use App\Models\Product\ProductFactory;
use App\Repository\ProductRepository;

class ProductResolver
{
    private ProductRepository $repository;

    public function __construct()
    {
        $this->repository = new ProductRepository();
    }

    public function getAll(?string $category = null): array
    {
        $rows = $this->repository->findAll($category);

        return array_map(
            fn(array $row) => $this->hydrateProduct($row)->toArray(),
            $rows
        );
    }

    public function getById(string $id): ?array
    {
        $row = $this->repository->findById($id);

        if ($row === null) {
            return null;
        }

        return $this->hydrateProduct($row)->toArray();
    }

    private function hydrateProduct(array $row): AbstractProduct
    {
        $product = ProductFactory::create($row);

        $attrRows      = $this->repository->findAttributesByProductId($row['id']);
        $attributesMap = [];

        foreach ($attrRows as $attrRow) {
            $attrKey = $attrRow['id'] . '_' . $attrRow['product_id'];

            if (!isset($attributesMap[$attrKey])) {
                $attributesMap[$attrKey] = [
                    'id'    => $attrRow['name'],
                    'name'  => $attrRow['name'],
                    'type'  => $attrRow['type'],
                    'items' => [],
                ];
            }

            if ($attrRow['item_id'] !== null) {
                $attributesMap[$attrKey]['items'][] = [
                    'id'           => $attrRow['item_id'],
                    'displayValue' => $attrRow['display_value'],
                    'value'        => $attrRow['item_value'],
                ];
            }
        }

        $product->setAttributes(array_values($attributesMap));

        $priceRows = $this->repository->findPricesByProductId($row['id']);

        $prices = array_map(
            fn(array $pr) => [
                'amount'   => (float) $pr['amount'],
                'currency' => [
                    'label'  => $pr['label'],
                    'symbol' => $pr['symbol'],
                ],
            ],
            $priceRows
        );

        $product->setPrices($prices);

        return $product;
    }
}
