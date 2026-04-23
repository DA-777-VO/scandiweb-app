<?php

declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\GraphQL\Queries\AllProductsQuery;
use App\GraphQL\Queries\ProductByIdQuery;
use App\GraphQL\Queries\ProductsByCategoryQuery;
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

    public function getAll(?string $category): array
    {
        $query = $category === null
            ? new AllProductsQuery()
            : new ProductsByCategoryQuery(ProductCategory::fromStringOrThrow($category));

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $this->repository->find($query);

        if (empty($rows)) {
            return [];
        }

        $productIds = array_column($rows, 'id');

        // Three batch queries — one per relation
        $galleryByProduct    = $this->repository->findGalleryByProductIds($productIds);
        $attributesByProduct = $this->repository->findAttributesByProductIds($productIds);
        $pricesByProduct     = $this->repository->findPricesByProductIds($productIds);

        return array_map(
            fn(array $row) => $this->hydrateProduct(
                $row,
                $galleryByProduct[$row['id']]    ?? [],
                $attributesByProduct[$row['id']] ?? [],
                $pricesByProduct[$row['id']]     ?? []
            )->toArray(),
            $rows
        );
    }

    public function getById(string $id): ?array
    {
        /** @var array<string, mixed>|null $row */
        $row = $this->repository->find(new ProductByIdQuery($id));

        if ($row === null) {
            return null;
        }

        $galleryByProduct    = $this->repository->findGalleryByProductIds([$id]);
        $attributesByProduct = $this->repository->findAttributesByProductIds([$id]);
        $pricesByProduct     = $this->repository->findPricesByProductIds([$id]);

        return $this->hydrateProduct(
            $row,
            $galleryByProduct[$id]    ?? [],
            $attributesByProduct[$id] ?? [],
            $pricesByProduct[$id]     ?? []
        )->toArray();
    }

    private function hydrateProduct(
        array $row,
        array $gallery,
        array $attributes,
        array $prices
    ): AbstractProduct {
        $product = AbstractProduct::create($row);
        $product->setGallery($gallery);
        $product->setAttributes($attributes);
        $product->setPrices($prices);
        return $product;
    }
}
