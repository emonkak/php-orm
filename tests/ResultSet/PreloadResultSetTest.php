<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\ResultSet\PreloadedResultSet;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\ResultSet\PreloadedResultSet
 */
class PreloadedResultSetTest extends TestCase
{
    public function testGetIterator(): void
    {
        $result = new PreloadedResultSet(['foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], iterator_to_array($result));
    }

    public function testGetSource(): void
    {
        $source = ['foo', 'bar'];
        $result = new PreloadedResultSet($source);
        $this->assertSame($source, $result->getSource());
    }
}
