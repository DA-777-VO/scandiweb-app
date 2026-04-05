<?php

declare(strict_types=1);

namespace App\Models\Product;

class ProductFactory
{
    public static function create(array $data): AbstractProduct
    {
        return match ($data['category'] ?? '') {
            'clothes' => new ClothesProduct($data),
            'tech' => new TechProduct($data),
            default => new ClothesProduct($data),
        };
    }
}
