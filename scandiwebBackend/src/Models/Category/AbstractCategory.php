<?php

declare(strict_types=1);

namespace App\Models\Category;

/**
 * Базовый класс категории.
 *
 * toArray() перенесён сюда из GeneralCategory — реализация одинакова
 * для всех возможных типов категорий, дублировать её в каждом подклассе
 * нет смысла. Подкласс переопределяет метод только если его поведение
 * действительно отличается.
 */
abstract class AbstractCategory
{
    protected int    $id;
    protected string $name;

    public function __construct(array $data)
    {
        $this->id   = (int) $data['id'];
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

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
