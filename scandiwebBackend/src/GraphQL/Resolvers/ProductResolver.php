<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Models\Product\AbstractProduct;
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

        if (empty($rows)) {
            return [];
        }

        // Собираем все product_id одним проходом
        $productIds = array_column($rows, 'id');

        // Два batch-запроса вместо N*2 запросов в цикле
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

        // Для одиночного продукта те же batch-методы (принимают массив)
        $attributesByProduct = $this->repository->findAttributesByProductIds([$id]);
        $pricesByProduct     = $this->repository->findPricesByProductIds([$id]);

        return $this->hydrateProduct(
            $row,
            $attributesByProduct[$id] ?? [],
            $pricesByProduct[$id]     ?? []
        )->toArray();
    }

    /**
     * Собирает объект продукта из строки БД + предзагруженных связей.
     * Не делает никаких запросов к БД — данные уже подготовлены Resolver-ом.
     */
    private function hydrateProduct(
        array $row,
        array $attributes,
        array $prices
    ): AbstractProduct {
        // AbstractProduct::create() — Static Factory Method с валидацией
        $product = AbstractProduct::create($row);
        $product->setAttributes($attributes);
        $product->setPrices($prices);
        return $product;
    }
}
