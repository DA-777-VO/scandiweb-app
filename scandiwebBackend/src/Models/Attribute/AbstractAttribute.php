<?php

declare(strict_types=1);

namespace App\Models\Attribute;

/**
 * Abstract base for all attribute types.
 *
 * Type is NOT stored as a field — each subclass declares it via abstract getType().
 * The type is intrinsic to the subclass, just like category is intrinsic to
 * ClothesProduct / TechProduct.
 *
 * Constructor is private — use create() as single entry point.
 * All validation happens in create(); constructor assigns already-validated data.
 */
abstract class AbstractAttribute
{
    protected string $id;
    protected string $name;
    protected array  $items;

    /**
     * Constructor receives already-validated data — no checks here.
     * private: only create() can instantiate subclasses.
     */
    private function __construct(string $id, string $name, array $items)
    {
        $this->id    = $id;
        $this->name  = $name;
        $this->items = $items;
    }

    // ── Static Factory ────────────────────────────────────────────────────────

    /**
     * Single entry point for creating attributes.
     * Validates ALL fields, then uses AttributeType enum to pick the subclass.
     *
     * @throws \InvalidArgumentException for any missing or invalid field
     */
    public static function create(array $data): static
    {
        if (!isset($data['id']) || $data['id'] === '') {
            throw new \InvalidArgumentException('Attribute "id" is required and must not be empty.');
        }

        if (!isset($data['name']) || $data['name'] === '') {
            throw new \InvalidArgumentException('Attribute "name" is required and must not be empty.');
        }

        if (!isset($data['type']) || $data['type'] === '') {
            throw new \InvalidArgumentException('Attribute "type" is required and must not be empty.');
        }

        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new \InvalidArgumentException('Attribute "items" is required and must be an array.');
        }

        // Enum validates type value and determines the subclass
        $type = AttributeType::fromStringOrThrow($data['type']);

        return match ($type) {
            AttributeType::Text   => new TextAttribute($data['id'], $data['name'], $data['items']),
            AttributeType::Swatch => new SwatchAttribute($data['id'], $data['name'], $data['items']),
        };
    }

    // ── Abstract ──────────────────────────────────────────────────────────────

    /**
     * Returns the type of this attribute.
     * Implemented by each subclass — the type is intrinsic to the class.
     */
    abstract public function getType(): AttributeType;

    // ── Getters ───────────────────────────────────────────────────────────────

    public function getId(): string    { return $this->id; }
    public function getName(): string  { return $this->name; }
    public function getItems(): array  { return $this->items; }

    // ── Formatting ────────────────────────────────────────────────────────────

    public function formatItems(): array
    {
        return array_map(
            fn(array $item) => [
                'id'           => $item['id'],
                'displayValue' => $item['displayValue'],
                'value'        => $item['value'],
            ],
            $this->items
        );
    }

    public function toArray(): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'type'  => $this->getType()->value,
            'items' => $this->formatItems(),
        ];
    }
}
