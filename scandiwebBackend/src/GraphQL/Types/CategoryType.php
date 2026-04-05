<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class CategoryType
{
    private static ?ObjectType $categoryType = null;

    public static function getType(): ObjectType
    {
        if (self::$categoryType === null) {
            self::$categoryType = new ObjectType([
                'name' => 'Category',
                'fields' => [
                    'id' => Type::int(),
                    'name' => Type::nonNull(Type::string()),
                ],
            ]);
        }
        return self::$categoryType;
    }
}
