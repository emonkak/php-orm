<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\AccessorCreators;
use Emonkak\Orm\Tests\Stubs\Entity;

/**
 * @cover Emonkak\Orm\Relation\AccessorCreators
 */
class AccessorCreatorsTest extends \PHPUnit_Framework_TestCase
{
    public function testToKeySelector()
    {
        $entity = new Entity();
        $entity->setFoo(123);
        $keySelector = AccessorCreators::toKeySelector('foo', Entity::class);
        $this->assertSame(123, $keySelector($entity));

        $entity = ['foo' => 123];
        $keySelector = AccessorCreators::toKeySelector('foo', null);
        $this->assertSame(123, $keySelector($entity));
    }

    public function testToPivotKeySelector()
    {
        $entity = new Entity();
        $entity->__pivot_foo = 123;
        $pivotKeySelector = AccessorCreators::toPivotKeySelector('__pivot_foo', Entity::class);
        $this->assertSame(123, $pivotKeySelector($entity));
        $this->assertFalse(isset($entity->__pivot_foo));

        $entity = ['__pivot_foo' => 123];
        $pivotKeySelector = AccessorCreators::toPivotKeySelector('__pivot_foo', null);
        $this->assertSame(123, $pivotKeySelector($entity));
        $this->assertFalse(isset($entity['__pivot_foo']));
    }

    public function testToKeyAssignee()
    {
        $entity = new Entity();
        $keyAssignee = AccessorCreators::toKeyAssignee('foo', Entity::class);
        $keyAssignee($entity, 123);
        $this->assertSame(123, $entity->getFoo());

        $entity = [];
        $keyAssignee = AccessorCreators::toKeyAssignee('foo', null);
        $entity = $keyAssignee($entity, 123);
        $this->assertTrue(isset($entity['foo']));
        $this->assertSame(123, $entity['foo']);
    }
}
