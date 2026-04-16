<?php

declare(strict_types=1);

namespace App\Models\Product;

/**
 * Продукт категории "tech".
 *
 * Класс намеренно пустой — он представляет конкретный тип продукта
 * для полиморфизма (требование ТЗ).
 *
 * Расширяется когда техника получает уникальные поля/поведение
 * (например: warranty, technical specs, compatibility и т.д.).
 */
final class TechProduct extends AbstractProduct
{
}
