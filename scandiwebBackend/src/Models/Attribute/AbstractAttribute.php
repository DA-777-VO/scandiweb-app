<?php

declare(strict_types=1);

namespace App\Models\Attribute;

abstract class AbstractAttribute
{
    protected string $id;
    protected string $name;
    protected string $type;
    protected array $items;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->type = $data['type'];
        $this->items = $data['items'] ?? [];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    abstract public function formatItems(): array;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'items' => $this->formatItems(),
        ];
    }
}
