<?php

declare(strict_types=1);

namespace App\Models\Attribute;

abstract class AbstractAttribute
{
    protected string $id;
    protected string $name;
    protected array  $items;

    private function __construct(string $id, string $name, array $items)
    {
        $this->id    = $id;
        $this->name  = $name;
        $this->items = $items;
    }

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

        $type = AttributeType::fromStringOrThrow($data['type']);

        return match ($type) {
            AttributeType::Text   => new TextAttribute($data['id'], $data['name'], $data['items']),
            AttributeType::Swatch => new SwatchAttribute($data['id'], $data['name'], $data['items']),
        };
    }

    abstract public function getType(): AttributeType;

    public function getId(): string    { return $this->id; }
    public function getName(): string  { return $this->name; }
    public function getItems(): array  { return $this->items; }

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
