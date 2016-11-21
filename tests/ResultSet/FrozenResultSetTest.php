<?php

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\ResultSet\FrozenResultSet;

/**
 * @covers Emonkak\Orm\ResultSet\FrozenResultSet
 */
class FrozenResultSetTest extends \PHPUnit_Framework_TestCase
{
    public function testGetClass()
    {
        $result = new FrozenResultSet([], \stdClass::class);
        $this->assertSame(\stdClass::class, $result->getClass());
    }

    public function testGetIterator()
    {
        $result = new FrozenResultSet(['foo', 'bar'], \stdClass::class);
        $this->assertEquals(['foo', 'bar'], iterator_to_array($result));
    }
}
