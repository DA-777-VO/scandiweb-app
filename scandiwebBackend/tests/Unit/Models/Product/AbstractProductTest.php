<?php

namespace Tests\Unit\Models\Product;

use PHPUnit\Framework\TestCase;
use App\Models\Product\AbstractProduct;
use App\Models\Product\ClothesProduct;
use App\Models\Product\TechProduct;
use App\Models\Product\ProductCategory;

class AbstractProductTest extends TestCase
{
    public function testCreatesClothesProduct(): void
    {
        $data = [
            'id' => 'pants',
            'name' => 'Pants',
            'in_stock' => true,
            'description' => 'Nice pants',
            'brand' => 'Brand X',
            'category' => 'clothes'
        ];

        $product = AbstractProduct::create($data);

        $this->assertInstanceOf(ClothesProduct::class, $product);
        $this->assertEquals(ProductCategory::Clothes, $product->getCategory());
        $this->assertEquals('pants', $product->getId());
        $this->assertEquals('Pants', $product->getName());
        $this->assertTrue($product->isInStock());
        $this->assertEquals('Nice pants', $product->getDescription());
        $this->assertEquals('Brand X', $product->getBrand());
    }

    public function testCreatesTechProduct(): void
    {
        $data = [
            'id' => 'mouse',
            'name' => 'Mouse',
            'in_stock' => false,
            'description' => 'A computer mouse',
            'brand' => 'TechBrand',
            'category' => 'tech'
        ];

        $product = AbstractProduct::create($data);

        $this->assertInstanceOf(TechProduct::class, $product);
        $this->assertEquals(ProductCategory::Tech, $product->getCategory());
        $this->assertFalse($product->isInStock());
    }

    public function testThrowsExceptionOnMissingId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product "id" is required and must not be empty.');

        AbstractProduct::create([
            'name' => 'Name',
            'in_stock' => true,
            'description' => 'Desc',
            'brand' => 'Brand',
            'category' => 'clothes'
        ]);
    }

    public function testThrowsExceptionOnEmptyId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        AbstractProduct::create([
            'id' => '',
            'name' => 'Name',
            'in_stock' => true,
            'description' => 'Desc',
            'brand' => 'Brand',
            'category' => 'clothes'
        ]);
    }

    public function testThrowsExceptionOnMissingCategory(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        AbstractProduct::create([
            'id' => '1',
            'name' => 'Name',
            'in_stock' => true,
            'description' => 'Desc',
            'brand' => 'Brand'
        ]);
    }

    public function testSetGalleryAndAttributes(): void
    {
        $data = [
            'id' => 'shirt',
            'name' => 'Shirt',
            'in_stock' => true,
            'description' => 'A shirt',
            'brand' => 'Brand',
            'category' => 'clothes'
        ];

        $product = AbstractProduct::create($data);

        $product->setGallery(['img1.jpg', 'img2.jpg']);
        $this->assertEquals(['img1.jpg', 'img2.jpg'], $product->getGallery());

        $product->setPrices([
            ['amount' => 10.99, 'currency' => ['label' => 'USD', 'symbol' => '$']]
        ]);
        $this->assertCount(1, $product->getPrices());

        // We can't easily test setAttributes here without mocking or creating Attribute array
    }
}
