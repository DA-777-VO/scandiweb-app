<?php

declare(strict_types=1);

namespace App\GraphQL;

use App\GraphQL\Mutations\OrderMutation;
use App\GraphQL\Resolvers\CategoryResolver;
use App\GraphQL\Resolvers\ProductResolver;
use App\GraphQL\Types\AttributeType;
use App\GraphQL\Types\CategoryType;
use App\GraphQL\Types\ProductType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class SchemaBuilder
{
    public static function build(): Schema
    {
        $categoryResolver = new CategoryResolver();
        $productResolver  = new ProductResolver();
        $orderMutation    = new OrderMutation();

        $attributeItemType = AttributeType::getItemType();
        $attributeSetType  = AttributeType::getSetType($attributeItemType);
        $currencyType      = ProductType::getCurrencyType();
        $priceType         = ProductType::getPriceType($currencyType);
        $productType       = ProductType::getType($attributeSetType, $priceType);
        $categoryType      = CategoryType::getType();

        $queryType = new ObjectType([
            'name'   => 'Query',
            'fields' => [

                'categories' => [
                    'type'    => Type::listOf($categoryType),
                    'resolve' => fn() => $categoryResolver->getAll(),
                ],

                'category' => [
                    'type'    => $categoryType,
                    'args'    => ['name' => Type::nonNull(Type::string())],
                    'resolve' => fn($root, array $args)
                        => $categoryResolver->getByName($args['name']),
                ],

                'products' => [
                    'type'    => Type::listOf($productType),
                    'args'    => ['category' => Type::string()],
                    // Null means "all" — ProductResolver handles the strategy selection
                    'resolve' => fn($root, array $args)
                        => $productResolver->getAll($args['category'] ?? null),
                ],

                'product' => [
                    'type'    => $productType,
                    'args'    => ['id' => Type::nonNull(Type::string())],
                    'resolve' => fn($root, array $args)
                        => $productResolver->getById($args['id']),
                ],

            ],
        ]);

        $orderItemInputType = new InputObjectType([
            'name'   => 'OrderItemInput',
            'fields' => [
                'productId'          => Type::nonNull(Type::string()),
                'quantity'           => Type::nonNull(Type::int()),
                'selectedAttributes' => Type::string(),
            ],
        ]);

        $mutationType = new ObjectType([
            'name'   => 'Mutation',
            'fields' => [

                'placeOrder' => [
                    'type'    => Type::boolean(),
                    'args'    => [
                        'items' => Type::nonNull(
                            Type::listOf(Type::nonNull($orderItemInputType))
                        ),
                    ],
                    'resolve' => fn($root, array $args)
                        => $orderMutation->placeOrder($args['items']),
                ],

            ],
        ]);

        return new Schema([
            'query'    => $queryType,
            'mutation' => $mutationType,
        ]);
    }
}
