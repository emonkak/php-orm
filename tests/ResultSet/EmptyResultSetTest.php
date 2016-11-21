<?php

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\ResultSet\EmptyResultSet;

/**
 * @covers Emonkak\Orm\ResultSet\EmptyResultSet
 */
class EmptyResultSetTest extends \PHPUnit_Framework_TestCase
{
    public function testGetClass()
    {
        $result = new EmptyResultSet(\stdClass::class);
        $this->assertSame(\stdClass::class, $result->getClass());
    }

    public function testGetIterator()
    {
        $result = new EmptyResultSet(\stdClass::class);
        $this->assertEquals([], iterator_to_array($result));
    }
}
