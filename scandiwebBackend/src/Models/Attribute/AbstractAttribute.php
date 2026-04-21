<?php

declare(strict_types=1);

namespace App\Models\Attribute;

/**
 * Абстрактная модель атрибута продукта.
 *
 * Применяет тот же паттерн Static Factory Method что и AbstractProduct:
 *   - конструктор private
 *   - create() — единственная точка входа, с валидацией и enum-контролем типа
 *
 * TextAttribute и SwatchAttribute намеренно пустые — они нужны как отдельные
 * типы для полиморфизма. formatItems() живёт здесь т.к. логика одинакова;
 * подклассы переопределят его когда их поведение начнёт различаться.
 */
abstract class AbstractAttribute
{
    protected string        $id;
    protected string        $name;
    protected AttributeType $type;
    protected array         $items;

    /**
     * Конструктор private — объекты создаются только через create().
     */
    private function __construct(array $data)
    {
        $this->id    = $data['id'];
        $this->name  = $data['name'];
        $this->items = $data['items'] ?? [];

        // Enum-валидация типа
        $this->type = AttributeType::fromString($data['type'] ?? '');
    }

    // ── Static Factory Method ─────────────────────────────────────────────────

    /**
     * Создаёт атрибут нужного подкласса на основе type.
     *
     * @param  array<string, mixed> $data
     * @throws \InvalidArgumentException при отсутствии обязательных полей или неизвестном type
     */
    public static function create(array $data): static
    {
        if (empty($data['id'])) {
            throw new \InvalidArgumentException('Attribute data must contain a non-empty "id".');
        }
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Attribute data must contain a non-empty "name".');
        }
        if (!isset($data['type'])) {
            throw new \InvalidArgumentException('Attribute data must contain "type".');
        }

        $type = AttributeType::fromString($data['type']);

        return match ($type) {
            AttributeType::Swatch => new SwatchAttribute($data),
            AttributeType::Text   => new TextAttribute($data),
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

    public function getType(): AttributeType
    {
        return $this->type;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    // ── Форматирование ────────────────────────────────────────────────────────

    /**
     * Форматирует варианты атрибута для GraphQL ответа.
     * Логика одинакова для text и swatch — различие только в семантике value.
     * Подклассы переопределяют этот метод когда их форматирование расходится.
     */
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
            'type'  => $this->type->value,
            'items' => $this->formatItems(),
        ];
    }
}
