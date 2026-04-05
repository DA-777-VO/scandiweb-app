<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Models\Attribute\AbstractAttribute;
use App\Models\Attribute\AttributeFactory;

abstract class AbstractProduct
{
    protected string $id;
    protected string $name;
    protected bool $inStock;
    protected array $gallery;
    protected string $description;
    protected string $category;
    protected array $attributes;
    protected array $prices;
    protected string $brand;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->inStock = (bool) $data['in_stock'];
        $this->gallery = json_decode($data['gallery'] ?? '[]', true) ?? [];
        $this->description = $data['description'] ?? '';
        $this->category = $data['category'] ?? '';
        $this->brand = $data['brand'] ?? '';
        $this->prices = [];
        $this->attributes = [];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isInStock(): bool
    {
        return $this->inStock;
    }

    public function getGallery(): array
    {
        return $this->gallery;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function setPrices(array $prices): void
    {
        $this->prices = $prices;
    }

    public function setAttributes(array $attributesData): void
    {
        $this->attributes = array_map(
            fn(array $attr) => AttributeFactory::create($attr),
            $attributesData
        );
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getPrices(): array
    {
        return $this->prices;
    }

    abstract public function toArray(): array;

    protected function baseToArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'inStock' => $this->inStock,
            'gallery' => $this->gallery,
            'description' => $this->description,
            'category' => $this->category,
            'brand' => $this->brand,
            'attributes' => array_map(
                fn(AbstractAttribute $attr) => $attr->toArray(),
                $this->attributes
            ),
            'prices' => $this->prices,
        ];
    }
}
