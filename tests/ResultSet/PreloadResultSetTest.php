<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\ResultSet\PreloadedResultSet;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\ResultSet\PreloadedResultSet
 */
class PreloadedResultSetTest extends TestCase
{
    public function testGetClass()
    {
        $result = new PreloadedResultSet([], \stdClass::class);
        $this->assertSame(\stdClass::class, $result->getClass());
    }

    public function testGetIterator()
    {
        $result = new PreloadedResultSet(['foo', 'bar'], \stdClass::class);
        $this->assertEquals(['foo', 'bar'], iterator_to_array($result));
    }

    public function testGetSource()
    {
        $source = ['foo', 'bar'];
        $result = new PreloadedResultSet($source, \stdClass::class);
        $this->assertSame($source, $result->getSource());
    }
}
