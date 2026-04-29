<?php

namespace Tests\Unit\Models\Product;

use PHPUnit\Framework\TestCase;
use App\Models\Product\ProductCategory;

class ProductCategoryTest extends TestCase
{
    public function testFromStringOrThrowValidClothes(): void
    {
        $category = ProductCategory::fromStringOrThrow('clothes');
        $this->assertEquals(ProductCategory::Clothes, $category);
    }

    public function testFromStringOrThrowValidTech(): void
    {
        $category = ProductCategory::fromStringOrThrow('tech');
        $this->assertEquals(ProductCategory::Tech, $category);
    }

    public function testFromStringOrThrowInvalidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown product category: 'invalid'");

        ProductCategory::fromStringOrThrow('invalid');
    }
}
