<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Models\Product\AbstractProduct;
use App\Models\Product\ProductCategory;
use App\Repository\ProductRepository;

class ProductResolver
{
    private ProductRepository $repository;

    public function __construct()
    {
        $this->repository = new ProductRepository();
    }

    /**
     * @param ProductCategory $category  Всегда enum — конвертация из ?string сделана в SchemaBuilder
     */
    public function getAll(ProductCategory $category): array
    {
        $rows = $this->repository->findAll($category);

        // Пустой результат — валидный ответ, не ошибка.
        // GraphQL вернёт {"data": {"products": []}}
        if (empty($rows)) {
            return [];
        }

        $productIds = array_column($rows, 'id');

        // Batch-запросы: исключения из repository (пустой массив) не могут
        // возникнуть здесь, т.к. мы проверили empty($rows) выше.
        $attributesByProduct = $this->repository->findAttributesByProductIds($productIds);
        $pricesByProduct     = $this->repository->findPricesByProductIds($productIds);

        return array_map(
            fn(array $row) => $this->hydrateProduct(
                $row,
                $attributesByProduct[$row['id']] ?? [],
                $pricesByProduct[$row['id']]     ?? []
            )->toArray(),
            $rows
        );
    }

    public function getById(string $id): ?array
    {
        $row = $this->repository->findById($id);

        if ($row === null) {
            return null;
        }

        $attributesByProduct = $this->repository->findAttributesByProductIds([$id]);
        $pricesByProduct     = $this->repository->findPricesByProductIds([$id]);

        return $this->hydrateProduct(
            $row,
            $attributesByProduct[$id] ?? [],
            $pricesByProduct[$id]     ?? []
        )->toArray();
    }

    private function hydrateProduct(
        array $row,
        array $attributes,
        array $prices
    ): AbstractProduct {
        $product = AbstractProduct::create($row);
        $product->setAttributes($attributes);
        $product->setPrices($prices);
        return $product;
    }
}
