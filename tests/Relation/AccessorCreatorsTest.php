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
    public function testToObjectKeySelector()
    {
        $entity = new Entity();
        $entity->setFoo(123);
        $keySelector = AccessorCreators::createKeySelector('foo', Entity::class);
        $this->assertSame(123, $keySelector($entity));
    }

    public function testToObjectPivotKeySelector()
    {
        $entity = new Entity();
        $entity->__pivot_foo = 123;
        $pivotKeySelector = AccessorCreators::createPivotKeySelector('__pivot_foo', Entity::class);
        $this->assertSame(123, $pivotKeySelector($entity));
        $this->assertFalse(isset($entity->__pivot_foo));
    }

    public function testToObjectKeyEraser()
    {
        $entity = new Entity();
        $entity->__foo = 123;
        $eraser = AccessorCreators::createKeyEraser('__foo', Entity::class);
        $entity = $eraser($entity);
        $this->assertFalse(isset($entity->_foo));
    }

    public function testToObjectKeyAssignee()
    {
        $entity = new Entity();
        $keyAssignee = AccessorCreators::createKeyAssignee('foo', Entity::class);
        $keyAssignee($entity, 123);
        $this->assertSame(123, $entity->getFoo());
    }

    public function testToArrayKeySelector()
    {
        $keySelector = AccessorCreators::createKeySelector('foo', null);
        $this->assertSame(123, $keySelector(['foo' => 123]));
    }

    public function testToArrayPivotKeySelector()
    {
        $array = ['__pivot_foo' => 123];
        $pivotKeySelector = AccessorCreators::createPivotKeySelector('__pivot_foo', null);
        $this->assertSame(123, $pivotKeySelector($array));
        $this->assertEquals([], $array);
    }

    public function testToArrayKeyEraser()
    {
        $array = ['__foo' => 123];
        $eraser = AccessorCreators::createKeyEraser('__foo', null);
        $this->assertEquals([], $eraser($array));
    }

    public function testToArrayKeyAssignee()
    {
        $array = [];
        $keyAssignee = AccessorCreators::createKeyAssignee('foo', null);
        $this->assertEquals(['foo' => 123], $keyAssignee($array, 123));
    }
}
