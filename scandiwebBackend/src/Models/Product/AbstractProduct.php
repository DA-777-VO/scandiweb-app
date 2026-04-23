<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Models\Attribute\AbstractAttribute;

/**
 * Abstract base for all product types.
 *
 * gallery is loaded separately (product_gallery table) and injected
 * via setGallery() — it is no longer part of the products table row.
 */
abstract class AbstractProduct
{
    protected string $id;
    protected string $name;
    protected bool   $inStock;
    protected array  $gallery     = [];
    protected string $description;
    protected string $brand;

    /** @var AbstractAttribute[] */
    protected array $attributes = [];

    /** @var array<int, array<string, mixed>> */
    protected array $prices = [];

    /**
     * Constructor receives already-validated data from create().
     * No validation here — create() is the single validation point.
     * private: only create() can instantiate subclasses.
     */
    private function __construct(array $data)
    {
        $this->id          = $data['id'];
        $this->name        = $data['name'];
        $this->inStock     = (bool) $data['in_stock'];
        $this->description = $data['description'];
        $this->brand       = $data['brand'];
        // gallery is not in $data — loaded via setGallery() after construction
    }

    // ── Static Factory ────────────────────────────────────────────────────────

    /**
     * Single entry point for creating products.
     * Validates ALL fields from the products table row.
     * gallery is NOT validated here — it comes from a separate table.
     *
     * @throws \InvalidArgumentException for any missing or invalid field
     */
    public static function create(array $data): static
    {
        if (!isset($data['id']) || $data['id'] === '') {
            throw new \InvalidArgumentException('Product "id" is required and must not be empty.');
        }

        if (!isset($data['name']) || $data['name'] === '') {
            throw new \InvalidArgumentException('Product "name" is required and must not be empty.');
        }

        if (!isset($data['in_stock'])) {
            throw new \InvalidArgumentException('Product "in_stock" is required.');
        }

        if (!isset($data['description'])) {
            throw new \InvalidArgumentException('Product "description" is required.');
        }

        if (!isset($data['brand']) || $data['brand'] === '') {
            throw new \InvalidArgumentException('Product "brand" is required and must not be empty.');
        }

        if (!isset($data['category']) || $data['category'] === '') {
            throw new \InvalidArgumentException('Product "category" is required and must not be empty.');
        }

        $category = ProductCategory::fromStringOrThrow($data['category']);

        return match ($category) {
            ProductCategory::Clothes => new ClothesProduct($data),
            ProductCategory::Tech    => new TechProduct($data),
        };
    }

    // ── Abstract ──────────────────────────────────────────────────────────────

    abstract public function getCategory(): ProductCategory;

    // ── Getters ───────────────────────────────────────────────────────────────

    public function getId(): string          { return $this->id; }
    public function getName(): string        { return $this->name; }
    public function isInStock(): bool        { return $this->inStock; }
    public function getGallery(): array      { return $this->gallery; }
    public function getDescription(): string { return $this->description; }
    public function getBrand(): string       { return $this->brand; }
    public function getAttributes(): array   { return $this->attributes; }
    public function getPrices(): array       { return $this->prices; }

    // ── Setters ───────────────────────────────────────────────────────────────

    /** @param string[] $urls Ordered list of image URLs from product_gallery table */
    public function setGallery(array $urls): void
    {
        $this->gallery = $urls;
    }

    public function setAttributes(array $attributesData): void
    {
        $this->attributes = array_map(
            fn(array $attr) => AbstractAttribute::create($attr),
            $attributesData
        );
    }

    public function setPrices(array $prices): void
    {
        $this->prices = $prices;
    }

    // ── Serialization ─────────────────────────────────────────────────────────

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'inStock'     => $this->inStock,
            'gallery'     => $this->gallery,
            'description' => $this->description,
            'category'    => $this->getCategory()->value,
            'brand'       => $this->brand,
            'attributes'  => array_map(
                fn(AbstractAttribute $attr) => $attr->toArray(),
                $this->attributes
            ),
            'prices' => $this->prices,
        ];
    }
}
