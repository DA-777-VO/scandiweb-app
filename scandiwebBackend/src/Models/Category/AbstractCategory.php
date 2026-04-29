<?php

declare(strict_types=1);

namespace App\Models\Category;

abstract class AbstractCategory
{
    protected int    $id;
    protected string $name;

    private function __construct(array $data)
    {
        $this->id   = (int) $data['id'];
        $this->name = $data['name'];
    }

    public static function create(array $data): static
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException('Category "id" is required.');
        }

        if (!isset($data['name']) || $data['name'] === '') {
            throw new \InvalidArgumentException('Category "name" is required and must not be empty.');
        }

        return new GeneralCategory($data);
    }

    public function getId(): int      { return $this->id; }
    public function getName(): string { return $this->name; }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
