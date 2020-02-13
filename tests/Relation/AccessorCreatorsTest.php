<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\AccessorCreators;
use Emonkak\Orm\Tests\Fixtures\Entity;
use PHPUnit\Framework\TestCase;

/**
 * @cover Emonkak\Orm\Relation\AccessorCreators
 */
class AccessorCreatorsTest extends TestCase
{
    public function testToObjectKeySelector(): void
    {
        $entity = new Entity();
        $entity->setFoo(123);
        $keySelector = AccessorCreators::createKeySelector(Entity::class, 'foo');
        $this->assertSame(123, $keySelector($entity));
    }

    public function testToObjectPivotKeySelector(): void
    {
        $entity = new Entity();
        $entity->__pivot_foo = 123;
        $pivotKeySelector = AccessorCreators::createPivotKeySelector(Entity::class, '__pivot_foo');
        $this->assertSame(123, $pivotKeySelector($entity));
        $this->assertFalse(isset($entity->__pivot_foo));
    }

    public function testToObjectKeyEraser(): void
    {
        $entity = new Entity();
        $entity->__foo = 123;
        $eraser = AccessorCreators::createKeyEraser(Entity::class, '__foo');
        $entity = $eraser($entity);
        $this->assertFalse(isset($entity->_foo));
    }

    public function testToObjectKeyAssignee(): void
    {
        $entity = new Entity();
        $keyAssignee = AccessorCreators::createKeyAssignee(Entity::class, 'foo');
        $keyAssignee($entity, 123);
        $this->assertSame(123, $entity->getFoo());
    }

    public function testToArrayKeySelector(): void
    {
        $keySelector = AccessorCreators::createKeySelector(null, 'foo');
        $this->assertSame(123, $keySelector(['foo' => 123]));
    }

    public function testToArrayPivotKeySelector(): void
    {
        $array = ['__pivot_foo' => 123];
        $pivotKeySelector = AccessorCreators::createPivotKeySelector(null, '__pivot_foo');
        $this->assertSame(123, $pivotKeySelector($array));
        $this->assertEquals([], $array);
    }

    public function testToArrayKeyEraser(): void
    {
        $array = ['__foo' => 123];
        $eraser = AccessorCreators::createKeyEraser(null, '__foo');
        $this->assertEquals([], $eraser($array));
    }

    public function testToArrayKeyAssignee(): void
    {
        $array = [];
        $keyAssignee = AccessorCreators::createKeyAssignee(null, 'foo');
        $this->assertEquals(['foo' => 123], $keyAssignee($array, 123));
    }
}
