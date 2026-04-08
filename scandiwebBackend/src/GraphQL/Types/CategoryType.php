<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class CategoryType
{
    public static function getType(): ObjectType
    {
        return new ObjectType([
            'name' => 'Category',
            'fields' => [
                'id' => Type::int(),
                'name' => Type::nonNull(Type::string()),
            ],
        ]);
    }
}
