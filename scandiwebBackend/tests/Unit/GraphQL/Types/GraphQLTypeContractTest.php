<?php

namespace Tests\Unit\GraphQL\Types;

use App\GraphQL\Types\AttributeType;
use App\GraphQL\Types\CategoryType;
use App\GraphQL\Types\ProductType;
use PHPUnit\Framework\TestCase;

class GraphQLTypeContractTest extends TestCase
{
    public function testProductTypeExposesRequiredFieldsFromRequirements(): void
    {
        $attributeItemType = AttributeType::getItemType();
        $attributeSetType = AttributeType::getSetType($attributeItemType);
        $currencyType = ProductType::getCurrencyType();
        $priceType = ProductType::getPriceType($currencyType);
        $productType = ProductType::getType($attributeSetType, $priceType);

        $this->assertSame('String!', (string) $productType->getField('id')->getType());
        $this->assertSame('String!', (string) $productType->getField('name')->getType());
        $this->assertSame('Boolean!', (string) $productType->getField('inStock')->getType());
        $this->assertSame('[String]', (string) $productType->getField('gallery')->getType());
        $this->assertSame('String', (string) $productType->getField('description')->getType());
        $this->assertSame('[AttributeSet]', (string) $productType->getField('attributes')->getType());
        $this->assertSame('[Price]', (string) $productType->getField('prices')->getType());
    }

    public function testAttributeTypeIsSeparateNestedGraphQLTypeWithItems(): void
    {
        $itemType = AttributeType::getItemType();
        $setType = AttributeType::getSetType($itemType);

        $this->assertSame('AttributeItem', $itemType->name);
        $this->assertSame('String!', (string) $itemType->getField('id')->getType());
        $this->assertSame('String!', (string) $itemType->getField('displayValue')->getType());
        $this->assertSame('String!', (string) $itemType->getField('value')->getType());

        $this->assertSame('AttributeSet', $setType->name);
        $this->assertSame('String!', (string) $setType->getField('id')->getType());
        $this->assertSame('String!', (string) $setType->getField('name')->getType());
        $this->assertSame('String!', (string) $setType->getField('type')->getType());
        $this->assertSame('[AttributeItem]', (string) $setType->getField('items')->getType());
    }

    public function testCategoryAndPriceTypesExposeFrontendContractFields(): void
    {
        $categoryType = CategoryType::getType();
        $currencyType = ProductType::getCurrencyType();
        $priceType = ProductType::getPriceType($currencyType);

        $this->assertSame('Category', $categoryType->name);
        $this->assertSame('Int', (string) $categoryType->getField('id')->getType());
        $this->assertSame('String!', (string) $categoryType->getField('name')->getType());

        $this->assertSame('Currency', $currencyType->name);
        $this->assertSame('String!', (string) $currencyType->getField('label')->getType());
        $this->assertSame('String!', (string) $currencyType->getField('symbol')->getType());

        $this->assertSame('Price', $priceType->name);
        $this->assertSame('Float!', (string) $priceType->getField('amount')->getType());
        $this->assertSame('Currency', (string) $priceType->getField('currency')->getType());
    }
}
