<?php

declare(strict_types=1);

namespace App\Models\Category;

/**
 * General product category (all, clothes, tech).
 * Intentionally empty — toArray() and create() live in AbstractCategory.
 * Extension point for category-specific behaviour (e.g. FeaturedCategory).
 */
final class GeneralCategory extends AbstractCategory
{
}
