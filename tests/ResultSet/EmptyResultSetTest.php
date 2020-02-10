<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\ResultSet\EmptyResultSet;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\ResultSet\EmptyResultSet
 */
class EmptyResultSetTest extends TestCase
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
