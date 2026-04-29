<?php

namespace Tests\Unit\Models\Attribute;

use PHPUnit\Framework\TestCase;
use App\Models\Attribute\AbstractAttribute;
use App\Models\Attribute\TextAttribute;
use App\Models\Attribute\SwatchAttribute;
use App\Models\Attribute\AttributeType;

class AbstractAttributeTest extends TestCase
{
    public function testCreatesTextAttribute(): void
    {
        $data = [
            'id' => 'size',
            'name' => 'Size',
            'type' => 'text',
            'items' => [
                ['id' => 's', 'displayValue' => 'Small', 'value' => 'S']
            ]
        ];

        $attribute = AbstractAttribute::create($data);

        $this->assertInstanceOf(TextAttribute::class, $attribute);
        $this->assertEquals(AttributeType::Text, $attribute->getType());
        $this->assertEquals('size', $attribute->getId());
        $this->assertEquals('Size', $attribute->getName());
        $this->assertCount(1, $attribute->getItems());
    }

    public function testCreatesSwatchAttribute(): void
    {
        $data = [
            'id' => 'color',
            'name' => 'Color',
            'type' => 'swatch',
            'items' => [
                ['id' => 'red', 'displayValue' => 'Red', 'value' => '#FF0000']
            ]
        ];

        $attribute = AbstractAttribute::create($data);

        $this->assertInstanceOf(SwatchAttribute::class, $attribute);
        $this->assertEquals(AttributeType::Swatch, $attribute->getType());
    }

    public function testThrowsExceptionOnMissingId(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        AbstractAttribute::create([
            'name' => 'Name',
            'type' => 'text',
            'items' => []
        ]);
    }

    public function testThrowsExceptionOnInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        AbstractAttribute::create([
            'id' => 'test',
            'name' => 'Name',
            'type' => 'invalid_type',
            'items' => []
        ]);
    }

    public function testFormatItems(): void
    {
        $data = [
            'id' => 'size',
            'name' => 'Size',
            'type' => 'text',
            'items' => [
                ['id' => 's', 'displayValue' => 'Small', 'value' => 'S', 'extra' => 'ignored']
            ]
        ];

        $attribute = AbstractAttribute::create($data);
        $formatted = $attribute->formatItems();

        $this->assertCount(1, $formatted);
        $this->assertArrayHasKey('id', $formatted[0]);
        $this->assertArrayHasKey('displayValue', $formatted[0]);
        $this->assertArrayHasKey('value', $formatted[0]);
        $this->assertArrayNotHasKey('extra', $formatted[0]);
    }

    public function testToArray(): void
    {
        $data = [
            'id' => 'size',
            'name' => 'Size',
            'type' => 'text',
            'items' => [
                ['id' => 's', 'displayValue' => 'Small', 'value' => 'S']
            ]
        ];

        $attribute = AbstractAttribute::create($data);
        $array = $attribute->toArray();

        $this->assertEquals('size', $array['id']);
        $this->assertEquals('Size', $array['name']);
        $this->assertEquals('text', $array['type']);
        $this->assertIsArray($array['items']);
    }
}
