<?php

declare(strict_types=1);

namespace App\Models\Category;

abstract class AbstractCategory
{
    protected int $id;
    protected string $name;

    public function __construct(array $data)
    {
        $this->id = (int) $data['id'];
        $this->name = $data['name'];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function toArray(): array;
}
