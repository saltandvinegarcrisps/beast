<?php

namespace Tests;

use Beast\Framework\Database\Entity;
use ErrorException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EntityTest extends TestCase
{
    public function testEntityGet(): void
    {
        $entity = new class(['foo' => 'bar']) extends Entity {
        };

        $this->assertEquals('bar', $entity->foo);
    }

    public function testEntityIsSet(): void
    {
        $entity = new class(['foo' => 'bar']) extends Entity {
        };

        $this->assertTrue(isset($entity->foo));
    }

    public function testEntitySet(): void
    {
        $entity = new class() extends Entity {
        };

        $entity->baz = 'qux';
        $this->assertEquals('qux', $entity->baz);
    }

    public function testEntityUnset(): void
    {
        $entity = new class(['foo' => 'bar']) extends Entity {
        };

        unset($entity->foo);

        $this->expectException(ErrorException::class);
        $entity->foo;
    }

    public function testEntityWithAttributes(): void
    {
        $entity = new class() extends Entity {
        };

        $result = $entity->withAttributes(['foo' => 'bar']);
        $this->assertEquals($entity, $result);
        $this->assertTrue(isset($entity->foo));
    }

    public function testEntitySetAttributes(): void
    {
        $entity = new class() extends Entity {
        };

        $entity->setAttributes(['foo' => 'bar']);
        $this->assertTrue(isset($entity->foo));
    }

    public function testEntityToArray(): void
    {
        $entity = new class(['foo' => 'bar', 'baz' => 'qux']) extends Entity {
            protected $guarded = ['baz'];
        };

        $result = $entity->toArray();
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('foo', $result);
    }

    public function testEntityToJson(): void
    {
        $entity = new class(['foo' => 'bar']) extends Entity {
        };

        $result = $entity->toJson();
        $this->assertEquals('{"foo":"bar"}', $result);
    }

    public function testEntityToJsonException(): void
    {
        $entity = new class(['random_bytes' => random_bytes(4)]) extends Entity {
        };

        $this->expectException(RuntimeException::class);
        $entity->toJson();
    }

    public function testEntityToString(): void
    {
        $entity = new class(['foo' => 'bar']) extends Entity {
        };

        $result = (string) $entity;
        $this->assertEquals('{"foo":"bar"}', $result);
    }
}
