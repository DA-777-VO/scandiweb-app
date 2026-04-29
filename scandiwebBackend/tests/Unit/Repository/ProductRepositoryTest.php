<?php

namespace Tests\Unit\Repository;

use App\GraphQL\Queries\AllProductsQuery;
use App\GraphQL\Queries\ProductByIdQuery;
use App\GraphQL\Queries\ProductQuery;
use App\GraphQL\Queries\ProductsByCategoryQuery;
use App\Models\Product\ProductCategory;
use App\Repository\ProductRepository;
use PHPUnit\Framework\TestCase;


class ProductRepositoryTest extends TestCase
{
    private \PDO $pdo;
    private ProductRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->createSchema();
        $this->seedData();
        $this->repository = $this->createRepository($this->pdo);
    }

    public function testFindDispatchesProductQueriesToCorrectSqlStrategy(): void
    {
        $all = $this->repository->find(new AllProductsQuery());
        $tech = $this->repository->find(new ProductsByCategoryQuery(ProductCategory::Tech));
        $single = $this->repository->find(new ProductByIdQuery('apollo-shirt'));

        $this->assertCount(2, $all);
        $this->assertCount(1, $tech);
        $this->assertSame('apollo-laptop', $tech[0]['id']);
        $this->assertSame('Apollo Shirt', $single['name']);
    }

    public function testFindRejectsUnknownProductQueryStrategy(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown ProductQuery type');

        $this->repository->find(new class implements ProductQuery {});
    }

    public function testLoadsProductRelationsInBatchesAndGroupsThemByProduct(): void
    {
        $gallery = $this->repository->findGalleryByProductIds(['apollo-shirt', 'apollo-laptop']);
        $attributes = $this->repository->findAttributesByProductIds(['apollo-shirt']);
        $prices = $this->repository->findPricesByProductIds(['apollo-shirt', 'apollo-laptop']);

        $this->assertSame(
            ['shirt-front.jpg', 'shirt-back.jpg'],
            $gallery['apollo-shirt']
        );
        $this->assertSame(['laptop.jpg'], $gallery['apollo-laptop']);

        $this->assertCount(2, $attributes['apollo-shirt']);
        $this->assertSame('Size', $attributes['apollo-shirt'][0]['name']);
        $this->assertSame('text', $attributes['apollo-shirt'][0]['type']);
        $this->assertSame(
            [
                ['id' => 's', 'displayValue' => 'Small', 'value' => 'S'],
                ['id' => 'm', 'displayValue' => 'Medium', 'value' => 'M'],
            ],
            $attributes['apollo-shirt'][0]['items']
        );
        $this->assertSame('Color', $attributes['apollo-shirt'][1]['name']);
        $this->assertSame('#44ff03', $attributes['apollo-shirt'][1]['items'][0]['value']);

        $this->assertSame(120.0, $prices['apollo-shirt'][0]['amount']);
        $this->assertSame(['label' => 'USD', 'symbol' => '$'], $prices['apollo-shirt'][0]['currency']);
        $this->assertSame(999.99, $prices['apollo-laptop'][0]['amount']);
    }

    public function testBatchRelationMethodsRejectEmptyProductLists(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('findGalleryByProductIds requires at least one product id');

        $this->repository->findGalleryByProductIds([]);
    }

    private function createRepository(\PDO $pdo): ProductRepository
    {
        $reflection = new \ReflectionClass(ProductRepository::class);
        $repository = $reflection->newInstanceWithoutConstructor();
        $property = $reflection->getProperty('pdo');
        $property->setAccessible(true);
        $property->setValue($repository, $pdo);

        return $repository;
    }

    private function createSchema(): void
    {
        $this->pdo->exec('
            CREATE TABLE products (
                id TEXT PRIMARY KEY,
                name TEXT NOT NULL,
                in_stock INTEGER NOT NULL,
                description TEXT NOT NULL,
                brand TEXT NOT NULL,
                category TEXT NOT NULL
            )
        ');
        $this->pdo->exec('
            CREATE TABLE product_gallery (
                product_id TEXT NOT NULL,
                url TEXT NOT NULL,
                sort_order INTEGER NOT NULL
            )
        ');
        $this->pdo->exec('
            CREATE TABLE attributes (
                id INTEGER NOT NULL,
                product_id TEXT NOT NULL,
                name TEXT NOT NULL,
                type TEXT NOT NULL
            )
        ');
        $this->pdo->exec('
            CREATE TABLE attribute_items (
                attribute_id INTEGER NOT NULL,
                product_id TEXT NOT NULL,
                id TEXT NOT NULL,
                display_value TEXT NOT NULL,
                value TEXT NOT NULL,
                sort_order INTEGER NOT NULL
            )
        ');
        $this->pdo->exec('
            CREATE TABLE currencies (
                id INTEGER PRIMARY KEY,
                label TEXT NOT NULL,
                symbol TEXT NOT NULL
            )
        ');
        $this->pdo->exec('
            CREATE TABLE prices (
                product_id TEXT NOT NULL,
                amount REAL NOT NULL,
                currency_id INTEGER NOT NULL
            )
        ');
    }

    private function seedData(): void
    {
        $this->pdo->exec("
            INSERT INTO products (id, name, in_stock, description, brand, category) VALUES
            ('apollo-shirt', 'Apollo Shirt', 1, 'A shirt', 'Apollo', 'clothes'),
            ('apollo-laptop', 'Apollo Laptop', 1, 'A laptop', 'Apollo', 'tech')
        ");
        $this->pdo->exec("
            INSERT INTO product_gallery (product_id, url, sort_order) VALUES
            ('apollo-shirt', 'shirt-back.jpg', 2),
            ('apollo-shirt', 'shirt-front.jpg', 1),
            ('apollo-laptop', 'laptop.jpg', 1)
        ");
        $this->pdo->exec("
            INSERT INTO attributes (id, product_id, name, type) VALUES
            (1, 'apollo-shirt', 'Size', 'text'),
            (2, 'apollo-shirt', 'Color', 'swatch')
        ");
        $this->pdo->exec("
            INSERT INTO attribute_items (attribute_id, product_id, id, display_value, value, sort_order) VALUES
            (1, 'apollo-shirt', 'm', 'Medium', 'M', 2),
            (1, 'apollo-shirt', 's', 'Small', 'S', 1),
            (2, 'apollo-shirt', 'green', 'Green', '#44ff03', 1)
        ");
        $this->pdo->exec("
            INSERT INTO currencies (id, label, symbol) VALUES
            (1, 'USD', '$')
        ");
        $this->pdo->exec("
            INSERT INTO prices (product_id, amount, currency_id) VALUES
            ('apollo-shirt', 120.00, 1),
            ('apollo-laptop', 999.99, 1)
        ");
    }
}
