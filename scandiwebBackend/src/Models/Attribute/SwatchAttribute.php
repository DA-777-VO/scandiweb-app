<?php

declare(strict_types=1);

namespace App\Models\Attribute;

/**
 * Цветовой атрибут-свотч (Color).
 *
 * Класс намеренно пустой — представляет конкретный тип для полиморфизма.
 * value содержит HEX-цвет (#44FF03), displayValue — название (Green).
 * Расширяется когда свотчи получают уникальное поведение
 * (например: валидация HEX-формата, конвертация цветовых моделей и т.д.).
 */
final class SwatchAttribute extends AbstractAttribute
{
}
