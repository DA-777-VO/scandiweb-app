<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class AttributeType
{
    private static ?ObjectType $itemType = null;
    private static ?ObjectType $setType = null;

    public static function getItemType(): ObjectType
    {
        if (self::$itemType === null) {
            self::$itemType = new ObjectType([
                'name' => 'AttributeItem',
                'fields' => [
                    'id' => Type::nonNull(Type::string()),
                    'displayValue' => Type::nonNull(Type::string()),
                    'value' => Type::nonNull(Type::string()),
                ],
            ]);
        }
        return self::$itemType;
    }

    public static function getSetType(ObjectType $itemType): ObjectType
    {
        if (self::$setType === null) {
            self::$setType = new ObjectType([
                'name' => 'AttributeSet',
                'fields' => [
                    'id' => Type::nonNull(Type::string()),
                    'name' => Type::nonNull(Type::string()),
                    'type' => Type::nonNull(Type::string()),
                    'items' => Type::listOf($itemType),
                ],
            ]);
        }
        return self::$setType;
    }
}
