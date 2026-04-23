<?php

declare(strict_types=1);

namespace App\Models\Category;

/**
 * Abstract base for all category types.
 *
 * Follows the same pattern as AbstractProduct and AbstractAttribute:
 * - constructor is private, create() is the single entry point
 * - all validation in create(), constructor assigns validated data
 * - subclasses declare their identity via abstract methods if needed
 */
abstract class AbstractCategory
{
    protected int    $id;
    protected string $name;

    /**
     * Constructor receives already-validated data.
     * private: only create() can instantiate subclasses.
     */
    private function __construct(array $data)
    {
        $this->id   = (int) $data['id'];
        $this->name = $data['name'];
    }

    // ── Static Factory ────────────────────────────────────────────────────────

    /**
     * Single entry point for creating categories.
     * Validates all fields before construction.
     *
     * @throws \InvalidArgumentException for missing or invalid fields
     */
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

    // ── Getters ───────────────────────────────────────────────────────────────

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
