<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ProductType
{
    private static ?ObjectType $currencyType = null;
    private static ?ObjectType $priceType = null;
    private static ?ObjectType $productType = null;

    public static function getCurrencyType(): ObjectType
    {
        if (self::$currencyType === null) {
            self::$currencyType = new ObjectType([
                'name' => 'Currency',
                'fields' => [
                    'label' => Type::nonNull(Type::string()),
                    'symbol' => Type::nonNull(Type::string()),
                ],
            ]);
        }
        return self::$currencyType;
    }

    public static function getPriceType(ObjectType $currencyType): ObjectType
    {
        if (self::$priceType === null) {
            self::$priceType = new ObjectType([
                'name' => 'Price',
                'fields' => [
                    'amount' => Type::nonNull(Type::float()),
                    'currency' => $currencyType,
                ],
            ]);
        }
        return self::$priceType;
    }

    public static function getType(ObjectType $attributeSetType, ObjectType $priceType): ObjectType
    {
        if (self::$productType === null) {
            self::$productType = new ObjectType([
                'name' => 'Product',
                'fields' => [
                    'id' => Type::nonNull(Type::string()),
                    'name' => Type::nonNull(Type::string()),
                    'inStock' => Type::nonNull(Type::boolean()),
                    'gallery' => Type::listOf(Type::string()),
                    'description' => Type::string(),
                    'category' => Type::string(),
                    'brand' => Type::string(),
                    'attributes' => Type::listOf($attributeSetType),
                    'prices' => Type::listOf($priceType),
                ],
            ]);
        }
        return self::$productType;
    }
}
