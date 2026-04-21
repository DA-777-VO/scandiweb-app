<?php

declare(strict_types=1);

namespace App\Models\Category;

/**
 * Общая категория товаров (all, clothes, tech).
 *
 * Класс намеренно пустой — toArray() унаследован из AbstractCategory,
 * так как поведение не отличается. Подкласс остаётся как точка расширения:
 * например FeaturedCategory может добавить поле $bannerImage и
 * переопределить toArray() не затрагивая этот класс.
 */
final class GeneralCategory extends AbstractCategory
{
}
