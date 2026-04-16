<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Models\Attribute\AbstractAttribute;

/**
 * Абстрактная модель продукта.
 *
 * Паттерн Static Factory Method:
 *   - конструктор private → нельзя создать объект снаружи через new
 *   - статический метод create() → единственная точка создания объектов
 *   - каждый подкласс вызывает parent::__construct() через static::new()
 *
 * Подклассы (ClothesProduct, TechProduct) остаются пустыми намеренно —
 * они представляют разные типы для полиморфизма (требование ТЗ),
 * даже если текущая логика одинакова. В будущем каждый расширяется отдельно.
 */
abstract class AbstractProduct
{
    protected string $id;
    protected string $name;
    protected bool   $inStock;
    protected array  $gallery;
    protected string $description;
    protected ProductCategory $category;
    protected string $brand;

    /** @var AbstractAttribute[] */
    protected array $attributes = [];

    /** @var array<int, array<string, mixed>> */
    protected array $prices = [];

    /**
     * Конструктор protected — используется только фабрикой create().
     * Фабрика уже преобразовала category в enum, поэтому повторной проверки здесь нет.
     */
    protected function __construct(array $data, ProductCategory $category)
    {
        $this->id          = $data['id'];
        $this->name        = $data['name'];
        $this->inStock     = (bool) ($data['in_stock'] ?? false);
        $this->gallery     = json_decode($data['gallery'] ?? '[]', true) ?? [];
        $this->description = $data['description'] ?? '';
        $this->brand       = $data['brand'] ?? '';
        $this->category    = $category;
    }

    // ── Static Factory Method ─────────────────────────────────────────────────

    /**
     * Единственная точка создания продуктов.
     * Валидирует обязательные поля, определяет тип через enum категории.
     *
     * @param  array<string, mixed> $data  Строка из БД
     * @throws \InvalidArgumentException   При отсутствии обязательных полей или неизвестной категории
     */
    public static function create(array $data): static
    {
        // Валидация обязательных полей
        if (empty($data['id'])) {
            throw new \InvalidArgumentException('Product data must contain a non-empty "id".');
        }
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Product data must contain a non-empty "name".');
        }
        if (!isset($data['category'])) {
            throw new \InvalidArgumentException('Product data must contain "category".');
        }

        // Преобразуем строку в enum один раз и используем его и для match(), и для модели.
        $category = ProductCategory::fromString($data['category']);

        return match ($category) {
            ProductCategory::Clothes => new ClothesProduct($data, $category),
            ProductCategory::Tech    => new TechProduct($data, $category),
        };
    }

    // ── Геттеры ───────────────────────────────────────────────────────────────

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

    public function getCategory(): ProductCategory
    {
        return $this->category;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getPrices(): array
    {
        return $this->prices;
    }

    // ── Сеттеры (вызываются из Resolver после загрузки связанных данных) ──────

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

    // ── Сериализация ──────────────────────────────────────────────────────────

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'inStock'     => $this->inStock,
            'gallery'     => $this->gallery,
            'description' => $this->description,
            'category'    => $this->category->value,
            'brand'       => $this->brand,
            'attributes'  => array_map(
                fn(AbstractAttribute $attr) => $attr->toArray(),
                $this->attributes
            ),
            'prices' => $this->prices,
        ];
    }
}
