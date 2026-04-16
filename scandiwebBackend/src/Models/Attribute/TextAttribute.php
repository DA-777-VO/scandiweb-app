<?php

declare(strict_types=1);

namespace App\Models\Attribute;

/**
 * Текстовый атрибут (Size, Capacity, With USB 3 ports и т.д.).
 *
 * Класс намеренно пустой — представляет конкретный тип для полиморфизма.
 * Логика formatItems() живёт в AbstractAttribute.
 * Расширяется когда текстовые атрибуты получают уникальное поведение
 * (например: валидация числовых значений, диапазоны размеров и т.д.).
 */
final class TextAttribute extends AbstractAttribute
{
}
