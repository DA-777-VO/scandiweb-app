<?php

namespace Tests\Unit\Models\Attribute;


use PHPUnit\Framework\TestCase;
use App\Models\Attribute\AttributeType;

class AttributeTypeTest extends TestCase
{
    public function testFromStringOrThrowValidText(): void
    {
        $type = AttributeType::fromStringOrThrow('text');
        $this->assertEquals(AttributeType::Text, $type);
    }

    public function testFromStringOrThrowValidSwatch(): void
    {
        $type = AttributeType::fromStringOrThrow('swatch');
        $this->assertEquals(AttributeType::Swatch, $type);
    }

    public function testFromStringOrThrowInvalidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown attribute type: 'invalid'");

        AttributeType::fromStringOrThrow('invalid');
    }
}
