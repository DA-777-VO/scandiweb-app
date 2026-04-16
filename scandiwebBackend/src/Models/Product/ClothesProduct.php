<?php

declare(strict_types=1);

namespace App\Models\Product;

/**
 * Продукт категории "clothes".
 *
 * Класс намеренно пустой — он представляет конкретный тип продукта
 * для полиморфизма (требование ТЗ: "abstract class for each model which
 * has different types, with differences handled in sub-classes").
 *
 * Общая логика живёт в AbstractProduct::toArray().
 * Этот класс расширяется когда одежда получает уникальные поля/поведение
 * (например: material, size chart, care instructions и т.д.).
 */
final class ClothesProduct extends AbstractProduct
{
}
