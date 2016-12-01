<?php

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\ResultSet\PreloadResultSet;

/**
 * @covers Emonkak\Orm\ResultSet\PreloadResultSet
 */
class PreloadResultSetTest extends \PHPUnit_Framework_TestCase
{
    public function testGetClass()
    {
        $result = new PreloadResultSet([], \stdClass::class);
        $this->assertSame(\stdClass::class, $result->getClass());
    }

    public function testGetIterator()
    {
        $result = new PreloadResultSet(['foo', 'bar'], \stdClass::class);
        $this->assertEquals(['foo', 'bar'], iterator_to_array($result));
    }

    public function testGetSource()
    {
        $source = ['foo', 'bar'];
        $result = new PreloadResultSet($source, \stdClass::class);
        $this->assertSame($source, $result->getSource());
    }
}
